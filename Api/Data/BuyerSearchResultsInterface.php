<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api\Data;

interface BuyerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Buyer list.
     * @return \Gr4vy\Magento\Api\Data\BuyerInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Gr4vy\Magento\Api\Data\BuyerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

