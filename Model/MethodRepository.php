<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Gr4vy\Magento\Api\Data\MethodInterfaceFactory;
use Gr4vy\Magento\Api\Data\MethodSearchResultsInterfaceFactory;
use Gr4vy\Magento\Api\MethodRepositoryInterface;
use Gr4vy\Magento\Model\ResourceModel\Method as ResourceMethod;
use Gr4vy\Magento\Model\ResourceModel\Method\CollectionFactory as MethodCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class MethodRepository implements MethodRepositoryInterface
{

    protected $resource;

    protected $methodFactory;

    protected $methodCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataMethodFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceMethod $resource
     * @param MethodFactory $methodFactory
     * @param MethodInterfaceFactory $dataMethodFactory
     * @param MethodCollectionFactory $methodCollectionFactory
     * @param MethodSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceMethod $resource,
        MethodFactory $methodFactory,
        MethodInterfaceFactory $dataMethodFactory,
        MethodCollectionFactory $methodCollectionFactory,
        MethodSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->methodFactory = $methodFactory;
        $this->methodCollectionFactory = $methodCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataMethodFactory = $dataMethodFactory;
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
        \Gr4vy\Magento\Api\Data\MethodInterface $method
    ) {
        /* if (empty($method->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $method->setStoreId($storeId);
        } */
        
        $methodData = $this->extensibleDataObjectConverter->toNestedArray(
            $method,
            [],
            \Gr4vy\Magento\Api\Data\MethodInterface::class
        );
        
        $methodModel = $this->methodFactory->create()->setData($methodData);
        
        try {
            $this->resource->save($methodModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the method: %1',
                $exception->getMessage()
            ));
        }
        return $methodModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($methodId)
    {
        $method = $this->methodFactory->create();
        $this->resource->load($method, $methodId);
        if (!$method->getId()) {
            throw new NoSuchEntityException(__('Method with id "%1" does not exist.', $methodId));
        }
        return $method->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->methodCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Gr4vy\Magento\Api\Data\MethodInterface::class
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
        \Gr4vy\Magento\Api\Data\MethodInterface $method
    ) {
        try {
            $methodModel = $this->methodFactory->create();
            $this->resource->load($methodModel, $method->getMethodId());
            $this->resource->delete($methodModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Method: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($methodId)
    {
        return $this->delete($this->get($methodId));
    }
}

