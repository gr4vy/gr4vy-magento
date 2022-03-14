<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Data;

use Gr4vy\Magento\Api\Data\BuyerInterface;

class Buyer extends \Magento\Framework\Api\AbstractExtensibleObject implements BuyerInterface
{
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
    public function getBuyerId()
    {
        return $this->_get(self::BUYER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setBuyerId($buyerId)
    {
        return $this->setData(self::BUYER_ID, $buyerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExternalIdentifier()
    {
        return $this->_get(self::EXTERNAL_IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     */
    public function setExternalIdentifier($external_identifier)
    {
        return $this->setData(self::EXTERNAL_IDENTIFIER, $external_identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->_get(self::DISPLAY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayName($display_name)
    {
        return $this->setData(self::DISPLAY_NAME, $display_name);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Gr4vy\Magento\Api\Data\BuyerExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
