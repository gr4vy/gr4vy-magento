<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Gr4vy\Magento\Api\Data\BuyerInterface;
use Gr4vy\Magento\Api\Data\BuyerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Buyer extends \Magento\Framework\Model\AbstractModel
{

    protected $buyerDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'gr4vy_buyers';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param BuyerInterfaceFactory $buyerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Gr4vy\Magento\Model\ResourceModel\Buyer $resource
     * @param \Gr4vy\Magento\Model\ResourceModel\Buyer\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        BuyerInterfaceFactory $buyerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Gr4vy\Magento\Model\ResourceModel\Buyer $resource,
        \Gr4vy\Magento\Model\ResourceModel\Buyer\Collection $resourceCollection,
        array $data = []
    ) {
        $this->buyerDataFactory = $buyerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve buyer model with buyer data
     * @return BuyerInterface
     */
    public function getDataModel()
    {
        $buyerData = $this->getData();
        
        $buyerDataObject = $this->buyerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $buyerDataObject,
            $buyerData,
            BuyerInterface::class
        );
        
        return $buyerDataObject;
    }
}

