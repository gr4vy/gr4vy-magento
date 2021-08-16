<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Payment;

use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;
use Gr4vy\Payment\Model\Client\Transaction as TransactionApi;
use Magento\Directory\Helper\Data as DirectoryHelper;

class Gr4vy extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_TYPE_AUTH = 'AUTHONLY';
    const PAYMENT_TYPE_AUCAP = 'AUTHNCAPTURE';

    /**
     * @var string
     */
    protected $_code = "gr4vy";

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCaptureOnce = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var TransactionApi
     */
    protected $transactionApi;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param DirectoryHelper $directory
     * @param Gr4vyHelper $gr4vyHelper
     * @param Gr4vyLogger $gr4vyLogger
     * @param TransactionApi $transactionApi
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger,
        TransactionApi $transactionApi
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->transactionApi = $transactionApi;
    }

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

    /**
     * Void payment - If the transaction was not yet captured the authorization will instead be voided.
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();

        $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
        $transaction_request = array(
            'amount' => intval($order->getGrandTotal() * 100)
        );

        // send refund request and retrieve response
        $response = $this->transactionApi->refund($gr4vy_transaction_id, $transaction_request);

        if ($response->getStatus() != 'refund_failed') {
            return $this;
        } else {
            throw new \Exception("Gr4vy voiding error.");
        }
    }

    /**
     * Capture payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
        $transaction_capture_request = array(
            'amount' => intval($amount * 100)
        );

        // send capture request and retrieve response
        $response = $this->transactionApi->capture($gr4vy_transaction_id, $transaction_capture_request);

        if ($response->getStatus() != 'capture_failed') {
            return $this;
        } else {
            throw new \Exception("Gr4vy capturing error.");
        }
    }

    /**
     * Refund capture
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();

        $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
        $transaction_refund_request = array(
            'amount' => intval($amount * 100)
        );

        // send refund request and retrieve response
        $response = $this->transactionApi->refund($gr4vy_transaction_id, $transaction_refund_request);

        if ($response->getStatus() != 'refund_failed') {
            return $this;
        } else {
            throw new \Exception("Gr4vy refunding error.");
        }
    }


    /**
     * Cancel payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->void($payment);

        return $this;
    }
}

