<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api\Data;

interface ServiceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Service list.
     * @return \Gr4vy\Payment\Api\Data\ServiceInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Gr4vy\Payment\Api\Data\ServiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

