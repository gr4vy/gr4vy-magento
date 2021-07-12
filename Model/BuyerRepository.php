<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model;

use Gr4vy\Payment\Api\BuyerRepositoryInterface;
use Gr4vy\Payment\Api\Data\BuyerInterfaceFactory;
use Gr4vy\Payment\Api\Data\BuyerSearchResultsInterfaceFactory;
use Gr4vy\Payment\Model\ResourceModel\Buyer as ResourceBuyer;
use Gr4vy\Payment\Model\ResourceModel\Buyer\CollectionFactory as BuyerCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class BuyerRepository implements BuyerRepositoryInterface
{

    protected $resource;

    protected $buyerFactory;

    protected $buyerCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataBuyerFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceBuyer $resource
     * @param BuyerFactory $buyerFactory
     * @param BuyerInterfaceFactory $dataBuyerFactory
     * @param BuyerCollectionFactory $buyerCollectionFactory
     * @param BuyerSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceBuyer $resource,
        BuyerFactory $buyerFactory,
        BuyerInterfaceFactory $dataBuyerFactory,
        BuyerCollectionFactory $buyerCollectionFactory,
        BuyerSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->buyerFactory = $buyerFactory;
        $this->buyerCollectionFactory = $buyerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBuyerFactory = $dataBuyerFactory;
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
        \Gr4vy\Payment\Api\Data\BuyerInterface $buyer
    ) {
        /* if (empty($buyer->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $buyer->setStoreId($storeId);
        } */
        
        $buyerData = $this->extensibleDataObjectConverter->toNestedArray(
            $buyer,
            [],
            \Gr4vy\Payment\Api\Data\BuyerInterface::class
        );
        
        $buyerModel = $this->buyerFactory->create()->setData($buyerData);
        
        try {
            $this->resource->save($buyerModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the buyer: %1',
                $exception->getMessage()
            ));
        }
        return $buyerModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($buyerId)
    {
        $buyer = $this->buyerFactory->create();
        $this->resource->load($buyer, $buyerId);
        if (!$buyer->getId()) {
            throw new NoSuchEntityException(__('Buyer with id "%1" does not exist.', $buyerId));
        }
        return $buyer->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->buyerCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Gr4vy\Payment\Api\Data\BuyerInterface::class
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
        \Gr4vy\Payment\Api\Data\BuyerInterface $buyer
    ) {
        try {
            $buyerModel = $this->buyerFactory->create();
            $this->resource->load($buyerModel, $buyer->getBuyerId());
            $this->resource->delete($buyerModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Buyer: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($buyerId)
    {
        return $this->delete($this->get($buyerId));
    }
}

