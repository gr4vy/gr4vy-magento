<?php

namespace Gr4vy\Magento\Observer;

use Gr4vy\Magento\Model\Payment\Gr4vy;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Order as OrderHelper;
use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Update order status after place order
 */
class OrderPlaceAfter implements ObserverInterface
{
    const NEW_ORDER_STATUS = Order::STATE_PENDING_PAYMENT;
    const ORDER_STATUS_PENDING = 'pending';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * OrderPlaceAfter constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param OrderHelper $orderHelper
     * @param Gr4vyLogger $gr4vyLogger
     * @param Gr4vyHelper $gr4vyHelper
     */
    public function __construct(
        OrderRepositoryInterface    $orderRepository,
        CartRepositoryInterface $cartRepository,
        TransactionRepositoryInterface $transactionRepository,
        OrderHelper $orderHelper,
        Gr4vyLogger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->transactionRepository = $transactionRepository;
        $this->orderHelper = $orderHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
    }

    /**
     * Update order status
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $payment = $order->getPayment();
        $quote = $observer->getEvent()->getQuote();

        if ($payment->getMethod() != Gr4vy::PAYMENT_METHOD_CODE) {
            $this->gr4vyLogger->logMixed(['Processing Non Gr4vy Order']);
            return;
        }
    
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            try {
                $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
                $transaction = $this->transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);

                // only applicable for gr4vy payment method
                if ($payment->getMethod() != Gr4vy::PAYMENT_METHOD_CODE
                    || strlen($gr4vy_transaction_id) < 1)
                {
                    return false;
                }

                $orderStatus = self::ORDER_STATUS_PENDING;
                $msg = __("");

                if ($this->gr4vyHelper->getGr4vyIntent() === Gr4vy::PAYMENT_TYPE_AUCAP) {
                    $orderStatus = Order::STATE_PROCESSING;
                    if (!$order->canInvoice()) {
                        $msg = __("Error in creating an Invoice.");
                    } else {
                        $msg = __(
                            "Captured amount of %1 online. Transaction ID: '%2'.",
                            $this->gr4vyHelper->formatCurrency($transaction->getCapturedAmount()/100),
                            strval($transaction->getGr4vyTransactionId())
                        );
                        $this->orderHelper->generatePaidInvoice($order, $gr4vy_transaction_id);
                    }
                } else if ($this->gr4vyHelper->getGr4vyIntent() === Gr4vy::PAYMENT_TYPE_AUTH) {
                    $msg = __(
                        "Authorized amount of %1 online. Transaction ID: '%2'.",
                        $this->gr4vyHelper->formatCurrency($transaction->getAmount()/100),
                        strval($transaction->getGr4vyTransactionId())
                    );
                }

                $this->orderHelper->updateOrderStatus($order, $orderStatus);
                $this->orderHelper->updateOrderHistoryData(
                    $order->getEntityId(),
                    $orderStatus,
                    $msg
                );

                $this->orderRepository->save($order);
                $this->disableQuote($quote);
                return true;        
            }
            catch (\Exception $e) {
                $this->gr4vyLogger->logException($e);
            }
        }
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
