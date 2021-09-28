<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Order extends AbstractHelper
{
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;

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
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService
    ) {
        parent::__construct($context);
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->_transaction = $transaction;
        $this->_invoiceService = $invoiceService;
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

