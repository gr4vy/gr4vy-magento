<?php

namespace Gr4vy\Payment\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ObserverInterface;
use Gr4vy\Payment\Api\TransactionRepositoryInterface;
use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;
use Gr4vy\Payment\Helper\Order as OrderHelper;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

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
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TransactionRepositoryInterface $transactionRepository,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger,
        OrderHelper $orderHelper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionRepository = $transactionRepository;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->orderHelper = $orderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $payment = $order->getPayment();
        $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
        $newOrderStatus = $this->gr4vyHelper->getGr4vyNewOrderStatus();
        if (empty($newOrderStatus)) {
            $newOrderStatus = \Magento\Sales\Model\Order::STATE_NEW;
        }

        // only applicable for gr4vy payment method
        if ($payment->getMethod() != \Gr4vy\Payment\Model\Payment\Gr4vy::PAYMENT_METHOD_CODE
            || strlen($gr4vy_transaction_id) < 1)
        {
            return;
        }
        $this->gr4vyLogger->logMixed([ 'method' => $payment->getMethod(), 'gr4vy_transaction_id' => $gr4vy_transaction_id ]);

        $transaction = $this->getTransaction($gr4vy_transaction_id);
        if ($this->gr4vyHelper->getGr4vyIntent() === \Gr4vy\Payment\Model\Payment\Gr4vy::PAYMENT_TYPE_AUCAP) {
            if (!$order->canInvoice()) {
                $msg = __("Error in creating an Invoice.");
            } else {
                $msg = __(
                    "Captured amount of %1 online. Transaction ID: '%2'.",
                    strip_tags($transaction->getCapturedAmount()),
                    strval($transaction->getGr4vyTransactionId())
                );
            }

            $this->orderHelper->generatePaidInvoice($order, $gr4vy_transaction_id);
            $this->orderHelper->updateOrderHistory($order, $msg, $newOrderStatus);
        }

        if ($this->gr4vyHelper->getGr4vyIntent() === \Gr4vy\Payment\Model\Payment\Gr4vy::PAYMENT_TYPE_AUTH) {
            $msg = __(
                "Authorized amount of %1 online. Transaction ID: '%2'.",
                strip_tags($transaction->getCapturedAmount()),
                strval($transaction->getGr4vyTransactionId())
            );
            $this->orderHelper->updateOrderHistory($order, $msg, $newOrderStatus);
        }
    }

    /**
     * retrieve buyer buy gr4vy transaction using gr4vy_transaction_id
     *
     * @param string
     * @return Gr4vy\Payment\Api\Data\TransactionInterface
     */
    public function getTransaction($gr4vy_transaction_id)
    {
        if (!empty($gr4vy_transaction_id)) {
            $transactionSearchCriteria = $this->searchCriteriaBuilder->addFilter('gr4vy_transaction_id', $gr4vy_transaction_id, 'eq')->create();
            $transactionSearchResults = $this->transactionRepository->getList($transactionSearchCriteria);

            if ($transactionSearchResults->getTotalCount() > 0) {
                list($item) = $transactionSearchResults->getItems();
                return $item;
            }
        }

        return null;
    }
}
