<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Block;

class Info extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @var \Gr4vy\Magento\Api\TransactionRepositoryInterface
     */
    protected $_transactionRepository;

    /**
     * @var \Gr4vy\Magento\Helper\Data
     */
    protected $_gr4vyHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Gr4vy\Magento\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Gr4vy\Magento\Helper\Data $gr4vyHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Gr4vy\Magento\Api\TransactionRepositoryInterface $transactionRepository,
        \Gr4vy\Magento\Helper\Data $gr4vyHelper,
        array $data = []
    ) {
        $this->_transactionRepository = $transactionRepository;
        $this->_gr4vyHelper = $gr4vyHelper;
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
        $gr4vy_transaction_id = $payment->getData('gr4vy_transaction_id');
        $transaction = $this->_transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);

        /*prepare labels*/
        $last_trans_id = (string)__('Last Transaction ID');
        $status = (string)__('Status');
        $amount = (string)__('Amount');
        $captured_amount = (string)__('Captured Amount');
        $refunded_amount = (string)__('Refunded Amount');
        $currency = (string)__('Currency');

        /*prepare data*/
        $captured = $transaction->getCapturedAmount() ? $this->_gr4vyHelper->formatCurrency($transaction->getCapturedAmount()/100) : 0;
        $refunded = $transaction->getRefundedAmount() ? $this->_gr4vyHelper->formatCurrency($transaction->getRefundedAmount()/100) : 0;
        $data = array(
            $last_trans_id => $transaction->getGr4vyTransactionId(),
            $status => ucwords(str_replace('_', ' ',$transaction->getStatus())),
            $amount => $this->_gr4vyHelper->formatCurrency($transaction->getAmount()/100),
            $captured_amount => $captured ?: '0.00',
            $refunded_amount => $refunded ?: '0.00',
            $currency => $transaction->getCurrency()
        );

        return $transport->addData($data);
    }
}

