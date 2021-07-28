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

class Base
{
    /**
     * @var Gr4vyHelper
     */
    protected $gr4vy_helper;

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
        Gr4vyHelper $gr4vy_helper,
        array $data = []
    ) {
        $this->buyerDataFactory = $buyerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->gr4vy_helper = $gr4vy_helper;
    }

    /**
     * generate token for current quote
     */
    public function generateToken()
    {
    }

    /**
     * prepare configuration values and assign to gr4vy configuration
     */
    private function initializeConfig()
    {
    }
}
