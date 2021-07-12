<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api\Data;

interface MethodInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ID = 'id';
    const METHOD_ID = 'method_id';

    /**
     * Get method_id
     * @return string|null
     */
    public function getMethodId();

    /**
     * Set method_id
     * @param string $methodId
     * @return \Gr4vy\Payment\Api\Data\MethodInterface
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
     * @return \Gr4vy\Payment\Api\Data\MethodInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Payment\Api\Data\MethodExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Payment\Api\Data\MethodExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Payment\Api\Data\MethodExtensionInterface $extensionAttributes
    );
}

