<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api\Data;

interface MethodInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ID = 'id';
    const METHOD_ID = 'method_id';
    const STATUS = 'status';
    const METHOD = 'method';
    const LABEL = 'label';
    const SCHEME = 'scheme';
    const EXTERNAL_IDENTIFIER = 'external_identifier';
    const EXPIRATION_DATE = 'expiration_date';
    const APPROVAL_URL = 'approval_url';
    const ENVIRONMENT = 'environment';

    /**
     * Get method_id
     * @return string|null
     */
    public function getMethodId();

    /**
     * Set method_id
     * @param string $methodId
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setMethodId($methodId);

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setId($id);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setStatus($status);

    /**
     * Get method
     * @return string|null
     */
    public function getMethod();

    /**
     * Set method
     * @param string $method
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setMethod($method);

    /**
     * Get label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     * @param string $label
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setLabel($label);

    /**
     * Get scheme
     * @return string|null
     */
    public function getScheme();

    /**
     * Set scheme
     * @param string $scheme
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setScheme($scheme);

    /**
     * Get external_identifier
     * @return string|null
     */
    public function getExternalIdentifier();

    /**
     * Set external_identifier
     * @param string $external_identifier
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setExternalIdentifier($external_identifier);

    /**
     * Get expiration_date
     * @return string|null
     */
    public function getExpirationDate();

    /**
     * Set expiration_date
     * @param string $expiration_date
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setExpirationDate($expiration_date);

    /**
     * Get approval_url
     * @return string|null
     */
    public function getApprovalUrl();

    /**
     * Set approval_url
     * @param string $approval_url
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setApprovalUrl($approval_url);

    /**
     * Get environment
     * @return string|null
     */
    public function getEnvironment();

    /**
     * Set environment
     * @param string $environment
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     */
    public function setEnvironment($environment);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Magento\Api\Data\MethodExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Magento\Api\Data\MethodExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Magento\Api\Data\MethodExtensionInterface $extensionAttributes
    );
}

