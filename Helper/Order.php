<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Helper;

use Gr4vy\model\Transaction;
use Gr4vy\model\Refund;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory;
use Magento\Sales\Model\Order\Status\HistoryFactory as OrderStatusHistoryFactory;

class Order extends AbstractHelper
{
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var Data
     */
    protected $gr4vyHelper;

    /**
     * @var Logger
     */
    protected $gr4vyLogger;

    /**
     * @var OrderStatusHistoryFactory
     */
    private $orderStatusHistoryFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderInterface|null
     */
    private $order;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $gr4vyHelper
     * @param Logger $gr4vyLogger
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Data $gr4vyHelper,
        Logger $gr4vyLogger,
        CollectionFactory $collectionFactory,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        OrderStatusHistoryFactory $orderStatusHistoryFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->collectionFactory = $collectionFactory;
        $this->_transaction = $transaction;
        $this->orderManagement = $orderManagement;
        $this->_invoiceService = $invoiceService;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * retrieve classified statuses
     *
     * @return array
     */
    public function getGr4vyTransactionStatuses()
    {
        $processing_statuses = [
            Transaction::STATUS_PROCESSING,
            Transaction::STATUS_CAPTURE_PENDING,
            Transaction::STATUS_AUTHORIZATION_SUCCEEDED,
            Transaction::STATUS_BUYER_APPROVAL_PENDING
        ];
        $cancel_statuses = [
            Transaction::STATUS_AUTHORIZATION_DECLINED,
            Transaction::STATUS_AUTHORIZATION_FAILED,
            Transaction::STATUS_AUTHORIZATION_VOIDED,
            Transaction::STATUS_AUTHORIZATION_VOID_PENDING
        ];
        $success_statuses = [
            Transaction::STATUS_CAPTURE_SUCCEEDED
        ];
        $refund_statuses = [
            Refund::STATUS_SUCCEEDED,
            Refund::STATUS_DECLINED,
            Refund::STATUS_FAILED,
            Refund::STATUS_VOIDED
        ];

        return [
            'processing' => $processing_statuses,
            'cancel' => $cancel_statuses,
            'success' => $success_statuses,
            'refund' => $refund_statuses
        ];
    }

    /**
     * update Order History
     *
     * @param \Magento\Sales\Model\Order
     * @param String
     * @param String
     */
    public function updateOrderHistory($order, $msg, $status)
    {
        $order->addStatusHistoryComment($msg);
        $order->setState($status)->setStatus($status);
        try {
            $order->save();
            $this->gr4vyLogger->logMixed(['msg' => $msg, 'status' => $status]);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * Update Order Status
     *
     * @param \Magento\Sales\Model\Order
     * @param String
     */
    public function updateOrderStatus($order, $status)
    {
        $order->setState($status)->setStatus($status);
        try {
            $order->save();
            $this->gr4vyLogger->logMixed(['status' => $status]);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * change order status by $gr4vy_transaction_id
     * NOTE: Collection being used because this function need to use Payment model to get Order later
     *
     * @param string
     * @return void
     */
    public function cancelOrderByGr4vyTransactionId($gr4vy_transaction_id)
    {
        if ($order = $this->getOrderByGr4vyTransactionId($gr4vy_transaction_id)) {
            // cancel order
            $this->orderManagement->cancel($order->getId());
        }
    }

    /**
     * Cancel Order
     *
     * @param int $orderId
     */
    public function cancelMagentoOrder($orderId)
    {
        $this->orderManagement->cancel($orderId);
    }

    /**
     * Update order history data
     *
     * @param int $orderId
     * @param string $orderStatus
     * @param string $orderComment
     * @param bool $isCustomerNotified
     */
    public function updateOrderHistoryData($orderId, $orderStatus, $orderComment, $isCustomerNotified = false)
    {
        try {
            $orderStatusHistory = $this->orderStatusHistoryFactory->create()
                ->setParentId($orderId)
                ->setEntityName('order')
                ->setStatus($orderStatus)
                ->setComment($orderComment)
                ->setIsCustomerNotified($isCustomerNotified);
            $this->orderManagement->addComment($orderId, $orderStatusHistory);
        } catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }


    /**
     * Retrieve magento order using gr4vy_transaction_id
     *
     * @param string
     * $return Magento\Sales\Model\Order
     */
    public function getOrderByGr4vyTransactionId($gr4vy_transaction_id)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('gr4vy_transaction_id', ['eq' => $gr4vy_transaction_id]);
        if ($collection->getSize() > 0) {
            $payment = $collection->getFirstItem();
            return $payment->getOrder();
        }

        return null;
    }

    /**
     * generate order invoice for captured gr4vy transaction
     *
     * @param \Magento\Sales\Model\Order
     * @param string
     */
    public function generatePaidInvoice($order, $gr4vy_transaction_id)
    {
        try {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            //set Gr4vy Transaction Id for this invoice
            $invoice->setData('transaction_id', $gr4vy_transaction_id);
            //set Invoice State to Paid
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();

            // set order payment amount paid
            $payment = $order->getPayment();
            $payment->setAmountPaid($invoice->getGrandTotal())->setCanRefund(1);
            $order->setTotalPaid($invoice->getGrandTotal())->setBaseTotalPaid($invoice->getGrandTotal());

            $this->_transaction->addObject($invoice)->addObject($order)->save();
            $this->gr4vyLogger->logMixed( ['generated' => 'Invoice #'. $invoice->getId() . ' for Transaction ' . $gr4vy_transaction_id . ' Captured']);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * @param $orderId
     * @return string|null
     */
    public function getIncrementId($orderId)
    {
        if (!$this->order) {
            $this->order = $this->orderRepository->get($orderId);
            return $this->order->getIncrementId();
        }
        return null;
    }
}

