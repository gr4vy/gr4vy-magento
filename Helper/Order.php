<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Helper;

use Gr4vy\Magento\Model\Transaction;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory;

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
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Data $gr4vyHelper,
        Logger $gr4vyLogger,
        CollectionFactory $collectionFactory,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService
    ) {
        parent::__construct($context);
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->collectionFactory = $collectionFactory;
        $this->_transaction = $transaction;
        $this->orderManagement = $orderManagement;
        $this->_invoiceService = $invoiceService;
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
            Transaction::STATUS_AUTHORIZATION_PENDING,
            Transaction::STATUS_BUYER_APPROVAL_SUCCEEDED,
            Transaction::STATUS_BUYER_APPROVAL_PENDING
        ];
        $cancel_statuses = [
            Transaction::STATUS_PROCESSING_FAILED,
            Transaction::STATUS_CAPTURE_DECLINED,
            Transaction::STATUS_CAPTURE_FAILED,
            Transaction::STATUS_AUTHORIZATION_DECLINED,
            Transaction::STATUS_AUTHORIZATION_FAILED,
            Transaction::STATUS_AUTHORIZATION_VOIDED,
            Transaction::STATUS_AUTHORIZATION_EXPIRED,
            Transaction::STATUS_AUTHORIZATION_VOID_PENDING,
            Transaction::STATUS_AUTHORIZATION_VOID_DECLINED,
            Transaction::STATUS_AUTHORIZATION_VOID_FAILED,
            Transaction::STATUS_BUYER_APPROVAL_DECLINED,
            Transaction::STATUS_BUYER_APPROVAL_FAILED,
            Transaction::STATUS_BUYER_APPROVAL_TIMEDOUT
        ];
        $success_statuses = [
            Transaction::STATUS_CAPTURE_SUCCEEDED
        ];
        $refund_statuses = [
            Transaction::STATUS_REFUND_SUCCEEDED,
            Transaction::STATUS_REFUND_PENDING,
            Transaction::STATUS_REFUND_DECLINED,
            Transaction::STATUS_REFUND_FAILED
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
            $invoice->setTransactionId($gr4vy_transaction_id);
            //set Invoice State to Paid
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
            $invoice->register();
            $this->_transaction->addObject($invoice)->addObject($order)->save();
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }
}

