<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Controller\Result\Json;

interface TransactionRepositoryInterface
{

    /**
     * Save Transaction
     * @param \Gr4vy\Magento\Api\Data\TransactionInterface $transaction
     * @return \Gr4vy\Magento\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Gr4vy\Magento\Api\Data\TransactionInterface $transaction
    );

    /**
     * Lock the basket before processing payment
     * @param string cartId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function lockBasket($cartId);

    /**
     * Process Gr4vy Tx and place an order in Magento
     * @param string cartId
     * @param string transactionId
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processTransaction(
        $cartId,
        $transactionId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Gr4vy\Magento\Api\Data\MethodInterface $methodData,
        \Gr4vy\Magento\Api\Data\ServiceInterface $serviceData,
        \Gr4vy\Magento\Api\Data\TransactionInterface $transactionData
    );

    /**
     * Set Guest Email - store a guest email against the session
     * @param string
     * @param string
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setGuestEmail(
        $cartId, 
        $email
    );

    /**
     * Retrieve Transaction
     * @param string $transactionId
     * @return \Gr4vy\Magento\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($transactionId);

    /**
     * retrieve buyer buy gr4vy transaction using gr4vy_transaction_id
     *
     * @param string
     * @return Gr4vy\Magento\Api\Data\TransactionInterface
     */
    public function getByGr4vyTransactionId($gr4vy_transaction_id);

    /**
     * Retrieve Transaction matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gr4vy\Magento\Api\Data\TransactionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Transaction
     * @param \Gr4vy\Magento\Api\Data\TransactionInterface $transaction
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Gr4vy\Magento\Api\Data\TransactionInterface $transaction
    );

    /**
     * Delete Transaction by ID
     * @param string $transactionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($transactionId);
}

