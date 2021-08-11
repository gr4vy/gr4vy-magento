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
    const GR4VY_PRIVATE_KEY = 'payment/gr4vy/private_key';
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
     * @return bool
     */
    public function isEnabled()
    {
        return true;
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
        return $this->scopeConfig->getValue(self::GR4VY_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve Gr4vy Environment
     *
     * @return string
     */
    public function getGr4vyEnvironment()
    {
        return $this->scopeConfig->getValue(self::GR4VY_ENV, ScopeInterface::SCOPE_STORE);
    }
}

