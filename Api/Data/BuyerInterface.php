<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api\Data;

interface BuyerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ID = 'id';
    const EXTERNAL_IDENTIFIER = 'external_identifier';
    const BUYER_ID = 'buyer_id';
    const DISPLAY_NAME = 'display_name';
    const BILLING_ADDRESS = 'billing_address';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set Id
     * @param string $id
     * @return \Gr4vy\Magento\Api\Data\BuyerInterface
     */
    public function setId($id);

    /**
     * Get buyer_id
     * @return string|null
     */
    public function getBuyerId();

    /**
     * Set buyer_id
     * @param string $buyer_id
     * @return \Gr4vy\Magento\Api\Data\BuyerInterface
     */
    public function setBuyerId($buyer_id);

    /**
     * Get external_identifier
     * @return string|null
     */
    public function getExternalIdentifier();

    /**
     * Set external_identifier
     * @param string $external_identifier
     * @return \Gr4vy\Magento\Api\Data\BuyerInterface
     */
    public function setExternalIdentifier($external_identifier);

    /**
     * Get display_name
     * @return string|null
     */
    public function getDisplayName();

    /**
     * Set display_name
     * @param string $display_name
     * @return \Gr4vy\Magento\Api\Data\BuyerInterface
     */
    public function setDisplayName($display_name);

    /**
     * Get billing_address
     * @return string|null
     */
    public function getBillingAddress();

    /**
     * Set billing_address
     * @param string $billing_address
     * @return \Gr4vy\Magento\Api\Data\BuyerInterface
     */
    public function setBillingAddress($billing_address);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Gr4vy\Magento\Api\Data\BuyerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Gr4vy\Magento\Api\Data\BuyerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Gr4vy\Magento\Api\Data\BuyerExtensionInterface $extensionAttributes
    );
}

