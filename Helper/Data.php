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
use Gr4vy\Magento\Api\Data\OptionsInterface;

class Data extends AbstractHelper
{
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
     * check payment debug enabled
     *
     * @return bool
     */
    public function isDebugOn()
    {
        return $this->getApiConfig(OptionsInterface::API_DEBUG);
    }

    /**
     * retrieve gr4vy payment instructions
     *
     * @return string
     */
    public function getPaymentInstructions()
    {
        return $this->getApiConfig(OptionsInterface::API_INSTRUCTION);
    }

    /**
     * retrieve relative path of private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->getApiConfig(OptionsInterface::API_PRIVATE_KEY);
    }

    /**
     * retrieve Gr4vy Id
     *
     * @return string
     */
    public function getGr4vyId()
    {
        return $this->getApiConfig(OptionsInterface::API_ID);
    }

    /**
     * retrieve Gr4vy Intent
     *
     * @return string
     */
    public function getGr4vyIntent()
    {
        return $this->getApiConfig(OptionsInterface::API_INTENT);
    }

    /**
     * retrieve Gr4vy Environment
     *
     * @return string
     */
    public function getGr4vyEnvironment()
    {
        return $this->getApiConfig(OptionsInterface::API_ENV);
    }

    /**
     * retrieve Gr4vy Store action
     *
     * @return string
     */
    public function getGr4vyPaymentStore()
    {
        return $this->getApiConfig(OptionsInterface::API_STORE);
    }

    /**
     * retrieve Gr4vy custom data action
     *
     * @return string
     */
    public function getGr4vyCustomData()
    {
        return $this->getOptionsConfig(OptionsInterface::OPTIONS_CUSTOM_DATA);
    }

    /**
     * retrieve Gr4vy New Order Status
     *
     * @return string
     */
    public function getGr4vyNewOrderStatus()
    {
        return $this->getApiConfig(OptionsInterface::API_ORDER_STATUS);
    }

    /**
     * get Payment Source
     *
     * @return string
     */
    public function getPaymentSource()
    {
        return $this->getOptionsConfig(OptionsInterface::OPTIONS_PAYMENT_SOURCE);
    }

    /**
     * build Theme config array
     *
     * @return array
     */
    public function buildThemeConfig()
    {
        $theme_config = [];
        if ($this->getThemeConfig(OptionsInterface::THEME_FONTS)) {
            $theme_config[OptionsInterface::THEME_FONTS] = [
                'body' => $this->getThemeConfig(OptionsInterface::THEME_FONT_BODY),
            ];
        }
        if ($this->getThemeConfig(OptionsInterface::THEME_COLORS)) {
            $theme_config[OptionsInterface::THEME_COLORS] = [
                'text' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_TEXT),
                'subtleText' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_SUBTLE_TEXT),
                'labelText' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_LABLE_TEXT),
                'primary' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_PRIMARY),
                'pageBackground' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_PAGE_BACKGROUND),
                'containerBackgroundUnchecked' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_CONTAINER_BACKGROUND_UNCHECKED),
                'containerBackground' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_CONTAINER_BACKGROUND),
                'containerBorder' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_CONTAINER_BORDER),
                'inputBorder' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INPUT_BORDER),
                'inputBackground' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INPUT_BACKGROUND),
                'inputText' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INPUT_TEXT),
                'inputRadioBorder' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INPUT_RADIO_BORDER),
                'inputRadioBorderChecked' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INPUT_RADIO_BORDER_CHECKED),
                'danger' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_DANGER),
                'dangerBackground' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_DANGER_BACKGROUND),
                'dangerText' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_DANGER_TEXT),
                'info' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INFO),
                'infoBackground' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INFO_BACKGROUND),
                'infoText' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_INFO_TEXT),
                'focus' => $this->getThemeConfig(OptionsInterface::THEME_COLOR_FOCUS),
            ];
        }
        if ($this->getThemeConfig(OptionsInterface::THEME_BORDERS)) {
            $theme_config['borderWidths'] = [
                'container' => $this->getThemeConfig(OptionsInterface::THEME_BORDER_CONTAINER),
                'input' => $this->getThemeConfig(OptionsInterface::THEME_BORDER_INPUT),
            ];
        }
        if ($this->getThemeConfig(OptionsInterface::THEME_RADII)) {
            $theme_config[OptionsInterface::THEME_RADII] = [
                'container' => $this->getThemeConfig(OptionsInterface::THEME_RADII_CONTAINER),
                'input' => $this->getThemeConfig(OptionsInterface::THEME_RADII_INPUT),
            ];
        }
        if ($this->getThemeConfig(OptionsInterface::THEME_FOCUS_RING)) {
            $theme_config['shadows'] = [
                'focusRing' => $this->getThemeConfig(OptionsInterface::THEME_FOCUS_RING_SHADOWS),
            ];
        }

        return $theme_config;
    }

    /**
     * build statement descriptor array
     *
     * @return array
     */
    public function buildStatementDescriptor()
    {
        $statement_descriptor = [];
        if ($this->getOptionsConfig(OptionsInterface::OPTIONS_STATEMENT_DESCRIPTOR)) {
            $statement_descriptor = [
                'name' => $this->getOptionsConfig(OptionsInterface::OPTIONS_SD_NAME),
                'description' => $this->getOptionsConfig(OptionsInterface::OPTIONS_SD_DESCRIPTION),
                'city' => $this->getOptionsConfig(OptionsInterface::OPTIONS_SD_CITY),
                'phone_number' => $this->getOptionsConfig(OptionsInterface::OPTIONS_SD_PHONE),
                'url' => $this->getOptionsConfig(OptionsInterface::OPTIONS_SD_URL),
            ];
        }

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
        return $this->getApiConfig(OptionsInterface::API_ENABLED);
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
        $isEnabled = $this->getApiConfig(OptionsInterface::API_ENABLED);
        $privateKey = $this->getPrivateKey();
        $classExist = class_exists('\Gr4vy\Gr4vyConfig');

        return $isEnabled && $privateKey && $classExist;
    }
}
