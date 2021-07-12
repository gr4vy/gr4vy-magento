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
     * Get method_id
     * @return string|null
     */
    public function getMethodId()
    {
        return $this->_get(self::METHOD_ID);
    }

    /**
     * Set method_id
     * @param string $methodId
     * @return \Gr4vy\Payment\Api\Data\MethodInterface
     */
    public function setMethodId($methodId)
    {
        return $this->setData(self::METHOD_ID, $methodId);
    }

    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Gr4vy\Payment\Api\Data\MethodInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
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
