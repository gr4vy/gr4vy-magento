<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Helper;

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
    const GR4VY_ENV = 'payment/gr4vy/environment';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
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
     * retrieve Gr4vy Environment
     *
     * @return string
     */
    public function getGr4vyEnvironment()
    {
        return (string) $this->scopeConfig->getValue(self::GR4VY_ENV, ScopeInterface::SCOPE_STORE);
    }
}

