<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model;

use Gr4vy\Payment\Api\Data\ServiceInterface;
use Gr4vy\Payment\Api\Data\ServiceInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Service extends \Magento\Framework\Model\AbstractModel
{

    protected $serviceDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'gr4vy_payment_service';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ServiceInterfaceFactory $serviceDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Gr4vy\Payment\Model\ResourceModel\Service $resource
     * @param \Gr4vy\Payment\Model\ResourceModel\Service\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ServiceInterfaceFactory $serviceDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Gr4vy\Payment\Model\ResourceModel\Service $resource,
        \Gr4vy\Payment\Model\ResourceModel\Service\Collection $resourceCollection,
        array $data = []
    ) {
        $this->serviceDataFactory = $serviceDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve service model with service data
     * @return ServiceInterface
     */
    public function getDataModel()
    {
        $serviceData = $this->getData();
        
        $serviceDataObject = $this->serviceDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $serviceDataObject,
            $serviceData,
            ServiceInterface::class
        );
        
        return $serviceDataObject;
    }
}

