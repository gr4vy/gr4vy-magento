<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api\Data;

interface BuyerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const EXTERNAL_IDENTIFIER = 'external_identifier';
    const BUYER_ID = 'buyer_id';

    /**
     * Get buyer_id
     * @return string|null
     */
    public function getBuyerId();

    /**
     * Set buyer_id
     * @param string $buyer_id
     * @return \Gr4vy\Payment\Api\Data\BuyerInterface
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
     * @return \Gr4vy\Payment\Api\Data\BuyerInterface
     */
    public function setExternalIdentifier($external_identifier);

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

