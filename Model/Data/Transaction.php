<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Data;

use Gr4vy\Magento\Api\Data\TransactionInterface;

class Transaction extends \Magento\Framework\Api\AbstractExtensibleObject implements TransactionInterface
{

    /**
     * Get transaction_id
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * Set transaction_id
     * @param string $transactionId
     * @return \Gr4vy\Magento\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
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
    public function getMethodId()
    {
        return $this->_get(self::METHOD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodId($method_id)
    {
        return $this->setData(self::METHOD_ID, $method_id);
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
    public function setBuyerId($buyer_id)
    {
        return $this->setData(self::BUYER_ID, $buyer_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceId()
    {
        return $this->_get(self::SERVICE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setServiceId($service_id)
    {
        return $this->setData(self::SERVICE_ID, $service_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getCapturedAmount()
    {
        return $this->_get(self::CAPTURED_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCapturedAmount($captured_amount)
    {
        return $this->setData(self::CAPTURED_AMOUNT, $captured_amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRefundedAmount()
    {
        return $this->_get(self::REFUNDED_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRefundedAmount($refunded_amount)
    {
        return $this->setData(self::REFUNDED_AMOUNT, $refunded_amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->_get(self::CURRENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::CURRENCY, $currency);
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
    public function getEnvironment()
    {
        return $this->_get(self::ENVIRONMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment($environment)
    {
        return $this->setData(self::ENVIRONMENT, $environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getGr4vyTransactionId()
    {
        return $this->_get(self::GR4VY_TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGr4vyTransactionId($gr4vy_transaction_id)
    {
        return $this->setData(self::GR4VY_TRANSACTION_ID, $gr4vy_transaction_id);
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
        \Gr4vy\Magento\Api\Data\TransactionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
