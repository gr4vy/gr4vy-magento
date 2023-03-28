<?php
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Checkout;

use Gr4vy\Magento\Model\Payment\Gr4vy;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Order as OrderHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Disable quote after successful payment
 */
class ProcessResponse extends AbstractModel
{
    const ORDER_STATUS_PENDING = 'pending';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var OrderPaymentRepositoryInterface
    */
    protected $orderPaymentRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * DisableQuote constructor.
     * @param Context $context
     * @param Registry $registry
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteRepository $quoteRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param Gr4vyHelper $gr4vyHelper
     * @param Gr4vyLogger $gr4vyLogger
     * @param OrderHelper $orderHelper
     * @param CheckoutSession $checkoutSession
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        CartRepositoryInterface $cartRepository,
        QuoteRepository $quoteRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger,
        OrderHelper $orderHelper,
        CheckoutSession $checkoutSession,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->cartRepository = $cartRepository;
        $this->quoteRepository = $quoteRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->checkoutSession = $checkoutSession;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->orderHelper = $orderHelper;
    }

    /**
     * Process Gr4vy response
     *
     * @param int $orderId
     * @throws NoSuchEntityException
     */
    public function processGr4vyResponse($orderId)
    {
        $quote = $this->quoteRepository->get($this->checkoutSession->getQuoteId());
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            $quotePaymentData = $quote->getPayment()->getData();
            /** @var Payment $payment */
            $payment = $order->getPayment();
            foreach ($quotePaymentData as $key => $value){
                switch ($key) {
                    case 'cc_type':
                        $payment->setCcType($value);
                        break;
                    case 'cc_exp_month':
                        $payment->setCcExpMonth($value);
                        break;
                    case 'cc_exp_year':
                        $payment->setCcExpYear($value);
                        break;
                    case 'cc_last_4':
                        $payment->setCcLast4($value);
                        break;
                }
            }
            $this->orderPaymentRepository->save($payment);
            $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
            // only applicable for gr4vy payment method
            if ($payment->getMethod() != Gr4vy::PAYMENT_METHOD_CODE
                || strlen($gr4vy_transaction_id) < 1)
            {
                return;
            }
            $this->gr4vyLogger->logMixed([ 'method' => $payment->getMethod(), 'gr4vy_transaction_id' => $gr4vy_transaction_id ]);

            $transaction = $this->transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);
            if ($this->gr4vyHelper->getGr4vyIntent() === Gr4vy::PAYMENT_TYPE_AUCAP) {
                // always set $newOrderStatus to processing if payment intent is authorize and capture
                $newOrderStatus = Order::STATE_PROCESSING;
                if (!$order->canInvoice()) {
                    $msg = __("Error in creating an Invoice.");
                } else {
                    $msg = __(
                        "Captured amount of %1 online. Transaction ID: '%2'.",
                        $this->gr4vyHelper->formatCurrency($transaction->getCapturedAmount()/100),
                        strval($transaction->getGr4vyTransactionId())
                    );
                }

                $this->orderHelper->generatePaidInvoice($order, $gr4vy_transaction_id);
                $this->orderHelper->updateOrderStatus($order, $newOrderStatus);
                $this->orderHelper->updateOrderHistoryData(
                    $order->getEntityId(),
                    $newOrderStatus,
                    $msg,
                    true
                );
            }

            else if ($this->gr4vyHelper->getGr4vyIntent() === Gr4vy::PAYMENT_TYPE_AUTH) {
                $this->orderHelper->updateOrderStatus($order, self::ORDER_STATUS_PENDING);
                $msg = __(
                    "Authorized amount of %1 online. Transaction ID: '%2'.",
                    $this->gr4vyHelper->formatCurrency($transaction->getAmount()/100),
                    strval($transaction->getGr4vyTransactionId())
                );
                $this->orderHelper->updateOrderHistoryData(
                    $order->getEntityId(),
                    self::ORDER_STATUS_PENDING,
                    $msg
                );
            }
        }
        $this->disableQuote($quote);
    }

    /**
     * Disable a quote after successful payment
     *
     * @param Quote $quote
     */
    public function disableQuote($quote)
    {
        if (!$quote || !$quote->getIsActive()) {
            return;
        }
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);
    }
}
