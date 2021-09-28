<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface MethodRepositoryInterface
{

    /**
     * Save Method
     * @param \Gr4vy\Magento\Api\Data\MethodInterface $method
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Gr4vy\Magento\Api\Data\MethodInterface $method
    );

    /**
     * Retrieve Method
     * @param string $methodId
     * @return \Gr4vy\Magento\Api\Data\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($methodId);

    /**
     * Retrieve Method matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gr4vy\Magento\Api\Data\MethodSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Method
     * @param \Gr4vy\Magento\Api\Data\MethodInterface $method
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Gr4vy\Magento\Api\Data\MethodInterface $method
    );

    /**
     * Delete Method by ID
     * @param string $methodId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($methodId);
}

