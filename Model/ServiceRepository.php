<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model;

use Gr4vy\Payment\Api\Data\ServiceInterfaceFactory;
use Gr4vy\Payment\Api\Data\ServiceSearchResultsInterfaceFactory;
use Gr4vy\Payment\Api\ServiceRepositoryInterface;
use Gr4vy\Payment\Model\ResourceModel\Service as ResourceService;
use Gr4vy\Payment\Model\ResourceModel\Service\CollectionFactory as ServiceCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class ServiceRepository implements ServiceRepositoryInterface
{

    protected $resource;

    protected $serviceFactory;

    protected $serviceCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataServiceFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceService $resource
     * @param ServiceFactory $serviceFactory
     * @param ServiceInterfaceFactory $dataServiceFactory
     * @param ServiceCollectionFactory $serviceCollectionFactory
     * @param ServiceSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceService $resource,
        ServiceFactory $serviceFactory,
        ServiceInterfaceFactory $dataServiceFactory,
        ServiceCollectionFactory $serviceCollectionFactory,
        ServiceSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->serviceFactory = $serviceFactory;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataServiceFactory = $dataServiceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Gr4vy\Payment\Api\Data\ServiceInterface $service
    ) {
        /* if (empty($service->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $service->setStoreId($storeId);
        } */
        
        $serviceData = $this->extensibleDataObjectConverter->toNestedArray(
            $service,
            [],
            \Gr4vy\Payment\Api\Data\ServiceInterface::class
        );
        
        $serviceModel = $this->serviceFactory->create()->setData($serviceData);
        
        try {
            $this->resource->save($serviceModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the service: %1',
                $exception->getMessage()
            ));
        }
        return $serviceModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($serviceId)
    {
        $service = $this->serviceFactory->create();
        $this->resource->load($service, $serviceId);
        if (!$service->getId()) {
            throw new NoSuchEntityException(__('Service with id "%1" does not exist.', $serviceId));
        }
        return $service->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->serviceCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Gr4vy\Payment\Api\Data\ServiceInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Gr4vy\Payment\Api\Data\ServiceInterface $service
    ) {
        try {
            $serviceModel = $this->serviceFactory->create();
            $this->resource->load($serviceModel, $service->getServiceId());
            $this->resource->delete($serviceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Service: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($serviceId)
    {
        return $this->delete($this->get($serviceId));
    }
}

