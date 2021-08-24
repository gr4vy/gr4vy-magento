<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model;

use Gr4vy\Payment\Api\Data\TransactionInterfaceFactory;
use Gr4vy\Payment\Api\Data\TransactionSearchResultsInterfaceFactory;
use Gr4vy\Payment\Api\TransactionRepositoryInterface;
use Gr4vy\Payment\Model\ResourceModel\Transaction as ResourceTransaction;
use Gr4vy\Payment\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class TransactionRepository implements TransactionRepositoryInterface
{

    protected $resource;

    protected $transactionFactory;

    protected $transactionCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataTransactionFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vy_logger;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param ResourceTransaction $resource
     * @param TransactionFactory $transactionFactory
     * @param TransactionInterfaceFactory $dataTransactionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param TransactionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Gr4vyLogger $gr4vy_logger
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     */
    public function __construct(
        ResourceTransaction $resource,
        TransactionFactory $transactionFactory,
        TransactionInterfaceFactory $dataTransactionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Gr4vyLogger $gr4vy_logger,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
    ) {
        $this->resource = $resource;
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTransactionFactory = $dataTransactionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->gr4vy_logger = $gr4vy_logger;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Gr4vy\Payment\Api\Data\TransactionInterface $transaction
    ) {
        /* if (empty($transaction->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $transaction->setStoreId($storeId);
        } */
        
        $transactionData = $this->extensibleDataObjectConverter->toNestedArray(
            $transaction,
            [],
            \Gr4vy\Payment\Api\Data\TransactionInterface::class
        );
        
        $transactionModel = $this->transactionFactory->create()->setData($transactionData);
        
        try {
            $this->resource->save($transactionModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the transaction: %1',
                $exception->getMessage()
            ));
        }
        return $transactionModel->getDataModel();
    }

    /**
     * associate transaction payment detail with magento payment object
     */
    public function setPaymentInformation(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Gr4vy\Payment\Api\Data\MethodInterface $methodData,
        \Gr4vy\Payment\Api\Data\ServiceInterface $serviceData,
        \Gr4vy\Payment\Api\Data\TransactionInterface $transactionData
    )
    {
        // 1. save transaction data
        $this->save($transactionData);

        // 2. set payment information
        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->getCartRepository();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteRepository->getActive($cartId);
        $payment = $quote->getPayment();
        $payment->setData('gr4vy_transaction_id', $transactionData->getGr4vyTransactionId())->save();
        $this->gr4vy_logger->logMixed($payment->getData());

        $quote_payment_id = $this->paymentMethodManagement->set($cartId, $paymentMethod);

        return true;
    }

    /**
     * Get Cart repository
     *
     * @return \Magento\Quote\Api\CartRepositoryInterface
     * @deprecated 100.2.0
     */
    private function getCartRepository()
    {
        if (!$this->cartRepository) {
            $this->cartRepository = ObjectManager::getInstance()
                ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        }
        return $this->cartRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($transactionId)
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transactionId);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction with id "%1" does not exist.', $transactionId));
        }
        return $transaction->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->transactionCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Gr4vy\Payment\Api\Data\TransactionInterface::class
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
        \Gr4vy\Payment\Api\Data\TransactionInterface $transaction
    ) {
        try {
            $transactionModel = $this->transactionFactory->create();
            $this->resource->load($transactionModel, $transaction->getTransactionId());
            $this->resource->delete($transactionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Transaction: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($transactionId)
    {
        return $this->delete($this->get($transactionId));
    }
}

