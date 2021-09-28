<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api\Data;

interface ServiceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const SERVICE_ID = 'service_id';
    const ID = 'id';
    const PAYMENT_SERVICE_DEFINITION_ID = 'payment_service_definition_id';
    const METHOD = 'method';
    const DISPLAY_NAME = 'display_name';
    const STATUS = 'status';
    const ACCEPTED_CURRENCIES = 'accepted_currencies';
    const ACCEPTED_COUNTRIES = 'accepted_countries';
    const ACTIVE = 'active';
    const POSITION = 'position';
    const ENVIRONMENT = 'environment';

    /**
     * Get service_id
     * @return string|null
     */
    public function getServiceId();

    /**
     * Set service_id
     * @param string $serviceId
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setServiceId($serviceId);

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setId($id);

    /**
     * Get payment_service_definition_id
     * @return string|null
     */
    public function getPaymentServiceDefinitionId();

    /**
     * Set payment_service_definition_id
     * @param string $id
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setPaymentServiceDefinitionId($payment_service_definition_id);

    /**
     * Get method
     * @return string|null
     */
    public function getMethod();

    /**
     * Set method
     * @param string $method
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setMethod($method);

    /**
     * Get display_name
     * @return string|null
     */
    public function getDisplayName();

    /**
     * Set display_name
     * @param string $display_name
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setDisplayName($display_name);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setStatus($status);

    /**
     * Get accepted_currencies
     * @return string|null
     */
    public function getAcceptedCurrencies();

    /**
     * Set accepted_currencies
     * @param string $accepted_countries
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setAcceptedCurrencies($accepted_countries);

    /**
     * Get accepted_countries
     * @return string|null
     */
    public function getAcceptedCountries();

    /**
     * Set accepted_countries
     * @param string $accepted_countries
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setAcceptedCountries($accepted_countries);

    /**
     * Get active
     * @return boolean|null
     */
    public function getActive();

    /**
     * Set active
     * @param boolean $active
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setActive($active);

    /**
     * Get position
     * @return integer|null
     */
    public function getPosition();

    /**
     * Set position
     * @param integer $position
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setPosition($position);

    /**
     * Get environment
     * @return string|null
     */
    public function getEnvironment();

    /**
     * Set environment
     * @param string $environment
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     */
    public function setEnvironment($environment);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Magento\Api\Data\ServiceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Magento\Api\Data\ServiceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Magento\Api\Data\ServiceExtensionInterface $extensionAttributes
    );
}

