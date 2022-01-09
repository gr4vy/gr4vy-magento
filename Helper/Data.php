<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const GR4VY_ENABLED = 'payment/gr4vy/active';
    const GR4VY_INSTRUCTION = 'payment/gr4vy/instructions';
    const GR4VY_PRIVATE_KEY = 'payment/gr4vy/private_key';
    const GR4VY_DEBUG = 'payment/gr4vy/debug';
    const GR4VY_ID = 'payment/gr4vy/id';
    const GR4VY_INTENT = 'payment/gr4vy/payment_action';
    const GR4VY_ORDER_STATUS = 'payment/gr4vy/order_status';
    const GR4VY_ENV = 'payment/gr4vy/environment';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * check payment method enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::GR4VY_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * check payment debug enabled
     *
     * @return bool
     */
    public function isDebugOn()
    {
        return (bool) $this->scopeConfig->getValue(self::GR4VY_DEBUG, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve gr4vy payment instructions
     *
     * @return string
     */
    public function getPaymentInstructions()
    {
        return (string) $this->scopeConfig->getValue(self::GR4VY_INSTRUCTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve relative path of private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->scopeConfig->getValue(self::GR4VY_PRIVATE_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve Gr4vy Id
     *
     * @return string
     */
    public function getGr4vyId()
    {
        return (string) $this->scopeConfig->getValue(self::GR4VY_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve Gr4vy Intent
     *
     * @return string
     */
    public function getGr4vyIntent()
    {
        return (string) $this->scopeConfig->getValue(self::GR4VY_INTENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve Gr4vy Environment
     *
     * @return string
     */
    public function getGr4vyEnvironment()
    {
        return (string) $this->scopeConfig->getValue(self::GR4VY_ENV, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve Gr4vy New Order Status
     *
     * @return string
     */
    public function getGr4vyNewOrderStatus()
    {
        return (string) $this->scopeConfig->getValue(self::GR4VY_ORDER_STATUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * If there is mask for quote, return unmasked quote_id.
     * Otherwise, return input param
     *
     * @param string
     * @return string
     */
    public function getQuoteIdFromMask($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        if ($quoteIdMask->getQuoteId()) {
            return $quoteIdMask->getQuoteId();
        }

        return $cartId;
    }

    /**
     * format currency with symbol and no container
     *
     * @param string | number
     * @return string
     */
    public function formatCurrency($amount)
    {
        return $this->_priceHelper->convertAndFormat($amount, false);
    }

    /**
     * placeholder to determine partial refund is available
     *
     * @return boolean
     */
    public function blockPartialRefund()
    {
        return $this->isEnabled();
    }

    /**
     * verify gr4vy payment ready status . it requires
     * 1. dependencies installed : gr4vy/gr4vy-php, php7.2+
     * 2. module enabled
     * 3. key uploaded
     *
     * @return boolean
     */
    public function checkGr4vyReady()
    {
        $isEnabled = $this->isEnabled();
        $privateKey = $this->getPrivateKey();
        $classExist = class_exists('\Gr4vy\Gr4vyConfig');

        return $isEnabled && $privateKey && $classExist;
    }
}
