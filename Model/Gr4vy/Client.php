<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Gr4vy;

use Gr4vy\Payment\Api\Data\BuyerInterface;
use Gr4vy\Payment\Api\Data\BuyerInterfaceFactory;
use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Magento\Framework\Api\DataObjectHelper;
use Gr4vy\api\BuyersApi;
use Gr4vy\model\BuyerRequest;

class Client extends Base
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
        BuyersApi $sdkBuyerApi,
        array $data = []
    ) {
        $this->buyerDataFactory = $buyerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sdkBuyerApi = $sdkBuyerApi;
        $this->sdkBuyerRequest = $sdkBuyerRequest;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function createBuyer($data)
    {
        $buyer = $this->sdkBuyerApi->addBuyer();

        // save buyer to gr4vy_buyers table
    }
}
