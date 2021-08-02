<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Block;

class Info extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @var \Gr4vy\Payment\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Gr4vy\Payment\Model\TransactionFactory $transactionFactory,
        array $data = []
    ) {
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * Don't show CC type for non-CC methods
     *
     * @return string|null
     */
    public function getCcTypeName()
    {
        return parent::getCcTypeName();
    }

    /**
     * @return string
     */
    public function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $info = $this->_transactionFactory->create();
        if ($this->getIsSecureMode()) {
            $info = $info->getPublicPaymentInfo($payment, true);
        } else {
            $info = $info->getPaymentInfo($payment, true);
        }
        return $transport->addData($info);
    }
}

