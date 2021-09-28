<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ServiceRepositoryInterface
{

    /**
     * Save Service
     * @param \Gr4vy\Magento\Api\Data\ServiceInterface $service
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Gr4vy\Magento\Api\Data\ServiceInterface $service
    );

    /**
     * Retrieve Service
     * @param string $serviceId
     * @return \Gr4vy\Magento\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($serviceId);

    /**
     * Retrieve Service matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gr4vy\Magento\Api\Data\ServiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Service
     * @param \Gr4vy\Magento\Api\Data\ServiceInterface $service
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Gr4vy\Magento\Api\Data\ServiceInterface $service
    );

    /**
     * Delete Service by ID
     * @param string $serviceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($serviceId);
}

