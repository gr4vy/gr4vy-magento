<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Gr4vy;

use Gr4vy\Payment\Api\Data\BuyerInterface;
use Gr4vy\Payment\Api\Data\BuyerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Base
{


    /**
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        BuyerInterfaceFactory $buyerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Gr4vy\Payment\Model\ResourceModel\Buyer $resource,
        \Gr4vy\Payment\Model\ResourceModel\Buyer\Collection $resourceCollection,
        array $data = []
    ) {
        $this->buyerDataFactory = $buyerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
