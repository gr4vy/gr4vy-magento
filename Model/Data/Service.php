<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Data;

use Gr4vy\Magento\Api\Data\ServiceInterface;

class Service extends \Magento\Framework\Api\AbstractExtensibleObject implements ServiceInterface
{

    /**
     * {@inheritdoc}
     */
    public function getServiceId()
    {
        return $this->_get(self::SERVICE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setServiceId($serviceId)
    {
        return $this->setData(self::SERVICE_ID, $serviceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentServiceDefinitionId() {
        return $this->_get(self::PAYMENT_SERVICE_DEFINITION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentServiceDefinitionId($payment_service_definition_id) {
        return $this->setData(self::PAYMENT_SERVICE_DEFINITION_ID, $payment_service_definition_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() {
        return $this->_get(self::METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method) {
        return $this->setData(self::METHOD, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName() {
        return $this->_get(self::DISPLAY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayName($display_name) {
        return $this->setData(self::DISPLAY_NAME, $display_name);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus() {
        return $this->_get(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status) {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getAcceptedCurrencies() {
        return $this->_get(self::ACCEPTED_CURRENCIES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAcceptedCurrencies($accepted_currencies) {
        return $this->setData(self::ACCEPTED_CURRENCIES, $accepted_currencies);
    }

    /**
     * {@inheritdoc}
     */
    public function getAcceptedCountries() {
        return $this->_get(self::ACCEPTED_COUNTRIES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAcceptedCountries($accepted_countries) {
        return $this->setData(self::ACCEPTED_COUNTRIES, $accepted_countries);
    }

    /**
     * {@inheritdoc}
     */
    public function getActive() {
        return $this->_get(self::ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setActive($active) {
        return $this->setData(self::ACTIVE, $active);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition() {
        return $this->_get(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position) {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment() {
        return $this->_get(self::ENVIRONMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment($environment) {
        return $this->setData(self::ENVIRONMENT, $environment);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Magento\Api\Data\ServiceExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Magento\Api\Data\ServiceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Magento\Api\Data\ServiceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
