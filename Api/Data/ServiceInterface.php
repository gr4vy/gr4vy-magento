<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api\Data;

interface ServiceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const SERVICE_ID = 'service_id';
    const ID = 'id';

    /**
     * Get service_id
     * @return string|null
     */
    public function getServiceId();

    /**
     * Set service_id
     * @param string $serviceId
     * @return \Gr4vy\Payment\Api\Data\ServiceInterface
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
     * @return \Gr4vy\Payment\Api\Data\ServiceInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Payment\Api\Data\ServiceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Payment\Api\Data\ServiceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Payment\Api\Data\ServiceExtensionInterface $extensionAttributes
    );
}

