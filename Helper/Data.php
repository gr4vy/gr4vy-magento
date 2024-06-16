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
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Gr4vy\Magento\Api\Data\OptionsInterface;
use Gr4vy\Magento\Helper\Magento246NonceProvider;

class Data extends AbstractHelper
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @param ProductMetadataInterface $productMetadata
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceHelper,
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $productMetadata,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_priceHelper = $priceHelper;
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
    }

    /**
     * retrieve a random generated nonce
     * @return string
     */
    public function getNonce()
    {
        $version = $this->productMetadata->getVersion();
        $cspNonceProvider = $this->objectManager->create(Magento246NonceProvider::class);

        if (version_compare($version, '2.4.7', '>=') && class_exists(\Magento\Csp\Helper\CspNonceProvider::class)) {
            $cspNonceProvider = $this->objectManager->create(\Magento\Csp\Helper\CspNonceProvider::class);
        }

        // $cspNonceProvider = $this->cspNonceProviderFactory->create();
        return $cspNonceProvider->generateNonce();
    }

    /**
     * retrieve config value by given section and key
     * @param string
     * @param string
     * @return string
     */
    public function getGr4vyConfigValue($section, $key)
    {
        $full_key = sprintf(OptionsInterface::TMPL, $section, $key);

        return $this->scopeConfig->getValue($full_key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * retrieve API config values
     *
     * @param string
     * @return string
     */
    public function getApiConfig($key)
    {
        return $this->getGr4vyConfigValue(OptionsInterface::SECTION_API, $key);
    }

    /**
     * retrieve Options config values
     *
     * @param string
     * @return string
     */
    public function getOptionsConfig($key)
    {
        return $this->getGr4vyConfigValue(OptionsInterface::SECTION_OPTIONS, $key);
    }

    /**
     * retrieve Theme config values
     *
     * @param string
     * @return string
     */
    public function getThemeConfig($key)
    {
        return $this->getGr4vyConfigValue(OptionsInterface::SECTION_THEME, $key);
    }

    /**
     * check payment method active status
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApiConfig(OptionsInterface::API['enabled']);
    }

    /**
     * check payment debug enabled
     *
     * @return bool
     */
    public function isDebugOn()
    {
        return $this->getApiConfig(OptionsInterface::API['debug']);
    }

    /**
     * retrieve gr4vy payment instructions
     *
     * @return string
     */
    public function getPaymentInstructions()
    {
        return $this->getApiConfig(OptionsInterface::API['instructions']);
    }

    /**
     * retrieve gr4vy payment method Title
     *
     * @return string
     */
    public function getPaymentTitle()
    {
        return $this->getApiConfig(OptionsInterface::API['title']);
    }

    /**
     * retrieve relative path of private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->getApiConfig(OptionsInterface::API['private_key']);
    }

    /**
     * retrieve Gr4vy Id
     *
     * @return string
     */
    public function getGr4vyId()
    {
        return $this->getApiConfig(OptionsInterface::API['id']);
    }

    /**
     * retrieve Gr4vy Intent
     *
     * @return string
     */
    public function getGr4vyIntent()
    {
        return $this->getApiConfig(OptionsInterface::API['intent']);
    }

    /**
     * retrieve Gr4vy Environment
     *
     * @return string
     */
    public function getGr4vyEnvironment()
    {
        return $this->getApiConfig(OptionsInterface::API['environment']);
    }

    /**
     * retrieve Gr4vy Store action
     *
     * @return string
     */
    public function getGr4vyPaymentStore()
    {
        return $this->getApiConfig(OptionsInterface::API['store']);
    }

    /**
     * retrieve Gr4vy custom data action
     *
     * @return string
     */
    public function getGr4vyCustomData()
    {
        return $this->getOptionsConfig(OptionsInterface::OPTIONS['custom_data']) ?: 'default';
    }

    /**
     * retrieve ask cvv
     *
     * @return string
     */
    public function getRequireSecurityCode()
    {
        return $this->getOptionsConfig(OptionsInterface::OPTIONS['require_security_code']);
    }

    /**
     * get Payment Source
     *
     * @return string
     */
    public function getPaymentSource()
    {
        return $this->getOptionsConfig(OptionsInterface::OPTIONS['payment_source']);
    }

    /**
     * build Theme config array
     *
     * @return array
     */
    public function buildThemeConfig()
    {
        $theme_config = [];
        $theme_config['fonts'] = [
            'body' => $this->getThemeConfig('fonts/body'),
        ];
        $theme_config['colors'] = [
            'text' => $this->getThemeConfig('colors/text'),
            'subtleText' => $this->getThemeConfig('colors/subtle_text'),
            'labelText' => $this->getThemeConfig('colors/label_text'),
            'primary' => $this->getThemeConfig('colors/primary'),
            'pageBackground' => $this->getThemeConfig('colors/page_background'),
            'containerBackgroundUnchecked' => $this->getThemeConfig('colors/container_background_unchecked'),
            'containerBackground' => $this->getThemeConfig('colors/container_background'),
            'containerBorder' => $this->getThemeConfig('colors/container_border'),
            'inputBorder' => $this->getThemeConfig('colors/input_border'),
            'inputBackground' => $this->getThemeConfig('colors/input_background'),
            'inputText' => $this->getThemeConfig('colors/input_text'),
            'inputRadioBorder' => $this->getThemeConfig('colors/input_radio_border'),
            'inputRadioBorderChecked' => $this->getThemeConfig('colors/input_radio_border_checked'),
            'danger' => $this->getThemeConfig('colors/danger'),
            'dangerBackground' => $this->getThemeConfig('colors/danger_background'),
            'dangerText' => $this->getThemeConfig('colors/danger_text'),
            'info' => $this->getThemeConfig('colors/info'),
            'infoBackground' => $this->getThemeConfig('colors/info_background'),
            'infoText' => $this->getThemeConfig('colors/info_text'),
            'focus' => $this->getThemeConfig('colors/focus'),
        ];
        $theme_config['borderWidths'] = [
            'container' => $this->getThemeConfig('border_widths/container'),
            'input' => $this->getThemeConfig('border_widths/input'),
        ];
        $theme_config['radii'] = [
            'container' => $this->getThemeConfig('radii/container'),
            'input' => $this->getThemeConfig('radii/input'),
        ];
        $theme_config['shadows'] = [
            'focusRing' => $this->getThemeConfig('shadows/focus_ring'),
        ];

        return $theme_config;
    }

    /**
     * build statement descriptor array
     *
     * @return array
     */
    public function buildStatementDescriptor()
    {
        $statement_descriptor = [
            'name' => $this->getOptionsConfig('statement_descriptor/name'),
            'description' => $this->getOptionsConfig('statement_descriptor/description'),
            'city' => $this->getOptionsConfig('statement_descriptor/city'),
            'phone_number' => $this->getOptionsConfig('statement_descriptor/phone_number'),
            'url' => $this->getOptionsConfig('statement_descriptor/url'),
        ];

        return $statement_descriptor;
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
        return $this->getApiConfig(OptionsInterface::API['enabled']);
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
        $isEnabled = $this->getApiConfig(OptionsInterface::API['enabled']);
        $privateKey = $this->getPrivateKey();
        $classExist = class_exists('\Gr4vy\Gr4vyConfig');

        return $isEnabled && $privateKey && $classExist;
    }
}
