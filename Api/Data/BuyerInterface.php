<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api\Data;

interface BuyerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ID = 'id';
    const BUYER_ID = 'buyer_id';

    /**
     * Get buyer_id
     * @return string|null
     */
    public function getBuyerId();

    /**
     * Set buyer_id
     * @param string $buyerId
     * @return \Gr4vy\Payment\Api\Data\BuyerInterface
     */
    public function setBuyerId($buyerId);

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Gr4vy\Payment\Api\Data\BuyerInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Payment\Api\Data\BuyerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Payment\Api\Data\BuyerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Payment\Api\Data\BuyerExtensionInterface $extensionAttributes
    );
}

