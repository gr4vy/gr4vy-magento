<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Gr4vy\Magento\Api\Data\MethodInterface;
use Gr4vy\Magento\Api\Data\MethodInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Method extends \Magento\Framework\Model\AbstractModel
{

    protected $methodDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'gr4vy_methods';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MethodInterfaceFactory $methodDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Gr4vy\Magento\Model\ResourceModel\Method $resource
     * @param \Gr4vy\Magento\Model\ResourceModel\Method\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MethodInterfaceFactory $methodDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Gr4vy\Magento\Model\ResourceModel\Method $resource,
        \Gr4vy\Magento\Model\ResourceModel\Method\Collection $resourceCollection,
        array $data = []
    ) {
        $this->methodDataFactory = $methodDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve method model with method data
     * @return MethodInterface
     */
    public function getDataModel()
    {
        $methodData = $this->getData();
        
        $methodDataObject = $this->methodDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $methodDataObject,
            $methodData,
            MethodInterface::class
        );
        
        return $methodDataObject;
    }
}

