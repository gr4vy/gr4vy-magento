<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Data;

use Gr4vy\Payment\Api\Data\ServiceInterface;

class Service extends \Magento\Framework\Api\AbstractExtensibleObject implements ServiceInterface
{

    /**
     * Get service_id
     * @return string|null
     */
    public function getServiceId()
    {
        return $this->_get(self::SERVICE_ID);
    }

    /**
     * Set service_id
     * @param string $serviceId
     * @return \Gr4vy\Payment\Api\Data\ServiceInterface
     */
    public function setServiceId($serviceId)
    {
        return $this->setData(self::SERVICE_ID, $serviceId);
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
     * @return \Gr4vy\Payment\Api\Data\ServiceInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Payment\Api\Data\ServiceExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Payment\Api\Data\ServiceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Payment\Api\Data\ServiceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
