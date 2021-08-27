<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BuyerRepositoryInterface
{

    /**
     * Save Buyer
     * @param \Gr4vy\Payment\Api\Data\BuyerInterface $buyer
     * @return \Gr4vy\Payment\Api\Data\BuyerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Gr4vy\Payment\Api\Data\BuyerInterface $buyer
    );

    /**
     * Retrieve Buyer
     * @param string $buyerId
     * @return \Gr4vy\Payment\Api\Data\BuyerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($buyerId);

    /**
     * retrieve buyer by external_identifier
     *
     * @param string
     * @return Gr4vy\Payment\Model\Buyer | null
     */
    public function getBuyerByExternalIdentifier($external_identifier);

    /**
     * Retrieve Buyer matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gr4vy\Payment\Api\Data\BuyerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Buyer
     * @param \Gr4vy\Payment\Api\Data\BuyerInterface $buyer
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Gr4vy\Payment\Api\Data\BuyerInterface $buyer
    );

    /**
     * Delete Buyer by ID
     * @param string $buyerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($buyerId);
}

