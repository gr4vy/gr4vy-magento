<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Api\Data;

interface MethodSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Method list.
     * @return \Gr4vy\Payment\Api\Data\MethodInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Gr4vy\Payment\Api\Data\MethodInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

