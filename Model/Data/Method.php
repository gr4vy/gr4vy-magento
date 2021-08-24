<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Data;

use Gr4vy\Payment\Api\Data\MethodInterface;

class Method extends \Magento\Framework\Api\AbstractExtensibleObject implements MethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMethodId()
    {
        return $this->_get(self::METHOD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodId($methodId)
    {
        return $this->setData(self::METHOD_ID, $methodId);
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
    public function getMethod() {
        return $this->_get(self::STATUS);
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
    public function getLabel() {
        return $this->_get(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label) {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme() {
        return $this->_get(self::SCHEME);
    }

    /**
     * {@inheritdoc}
     */
    public function setScheme($scheme) {
        return $this->setData(self::SCHEME, $scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function getExternalIdentifier() {
        return $this->_get(self::EXTERNAL_IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     */
    public function setExternalIdentifier($external_identifier) {
        return $this->setData(self::EXTERNAL_IDENTIFIER, $external_identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate() {
        return $this->_get(self::EXPIRATION_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpirationDate($expiration_date) {
        return $this->setData(self::EXPIRATION_DATE, $expiration_date);
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovalUrl() {
        return $this->_get(self::APPROVAL_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setApprovalUrl($approval_url) {
        return $this->setData(self::APPROVAL_URL, $approval_url);
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
     * @return \Gr4vy\Payment\Api\Data\MethodExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Payment\Api\Data\MethodExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Payment\Api\Data\MethodExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
