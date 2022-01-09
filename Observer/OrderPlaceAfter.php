<?php

namespace Gr4vy\Magento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Order as OrderHelper;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

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
     * @param Gr4vyHelper $gr4vyHelper
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger,
        OrderHelper $orderHelper
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->orderHelper = $orderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            $order = $observer->getEvent()->getData('order');
            $payment = $order->getPayment();
            $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
            $newOrderStatus = $this->gr4vyHelper->getGr4vyNewOrderStatus();

            if (empty($newOrderStatus)) {
                // default new order status in code is processing
                $newOrderStatus = \Magento\Sales\Model\Order::STATE_PROCESSING;
            }

            // only applicable for gr4vy payment method
            if ($payment->getMethod() != \Gr4vy\Magento\Model\Payment\Gr4vy::PAYMENT_METHOD_CODE
                || strlen($gr4vy_transaction_id) < 1)
            {
                return;
            }
            $this->gr4vyLogger->logMixed([ 'method' => $payment->getMethod(), 'gr4vy_transaction_id' => $gr4vy_transaction_id ]);

            $transaction = $this->transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);
            if ($this->gr4vyHelper->getGr4vyIntent() === \Gr4vy\Magento\Model\Payment\Gr4vy::PAYMENT_TYPE_AUCAP) {
                // always set $newOrderStatus to processing if payment intent is authorize and capture
                $newOrderStatus = \Magento\Sales\Model\Order::STATE_PROCESSING;

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
                $this->orderHelper->updateOrderHistory($order, $msg, $newOrderStatus);
            }

            if ($this->gr4vyHelper->getGr4vyIntent() === \Gr4vy\Magento\Model\Payment\Gr4vy::PAYMENT_TYPE_AUTH) {
                $msg = __(
                    "Authorized amount of %1 online. Transaction ID: '%2'.",
                    $this->gr4vyHelper->formatCurrency($transaction->getAmount()/100),
                    strval($transaction->getGr4vyTransactionId())
                );
                $this->orderHelper->updateOrderHistory($order, $msg, $newOrderStatus);
            }
        }
    }
}
