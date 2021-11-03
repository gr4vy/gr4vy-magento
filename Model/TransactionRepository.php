<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Gr4vy\Magento\Api\Data\TransactionInterfaceFactory;
use Gr4vy\Magento\Api\Data\TransactionSearchResultsInterfaceFactory;
use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Gr4vy\Magento\Model\ResourceModel\Transaction as ResourceTransaction;
use Gr4vy\Magento\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Gr4vy\Magento\Model\Client\Embed as Gr4vyEmbed;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
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
use Magento\Framework\Api\SearchCriteriaBuilder;

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
    protected $gr4vyLogger;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Gr4vyEmbed
     */
    protected $embedApi;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

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
     * @param Gr4vyLogger $gr4vyLogger
     * @param Gr4vyHelper $gr4vyHelper
     * @param Gr4vyEmbed $embedApi
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
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
        Gr4vyLogger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyEmbed $embedApi,
        SearchCriteriaBuilder $searchCriteriaBuilder,
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
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->embedApi = $embedApi;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Gr4vy\Magento\Api\Data\TransactionInterface $transaction
    ) {
        /* if (empty($transaction->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $transaction->setStoreId($storeId);
        } */
        
        $transactionData = $this->extensibleDataObjectConverter->toNestedArray(
            $transaction,
            [],
            \Gr4vy\Magento\Api\Data\TransactionInterface::class
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
     * {@inheritdoc}
     */
    public function setPaymentInformation(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Gr4vy\Magento\Api\Data\MethodInterface $methodData,
        \Gr4vy\Magento\Api\Data\ServiceInterface $serviceData,
        \Gr4vy\Magento\Api\Data\TransactionInterface $transactionData
    )
    {
        // 1. save transaction data
        $this->save($transactionData);

        // 2. set payment information
        $quote = $this->getQuoteModel($cartId);
        $payment = $quote->getPayment();
        $payment->setData('gr4vy_transaction_id', $transactionData->getGr4vyTransactionId())->save();
        $this->gr4vyLogger->logMixed($payment->getData());

        $quote_payment_id = $this->paymentMethodManagement->set($cartId, $paymentMethod);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmbedToken($cartId)
    {
        $quote = $this->getQuoteModel($cartId);
        $currency = $quote->getStore()->getCurrentCurrency()->getCode();
        $buyer_id = $quote->getData('gr4vy_buyer_id');

        // NOTE: $quote->getGrandTotal after shipping method specified contains calculated shipping amount
        $quote_total = $quote->getGrandTotal();

        $result = array();
        $result['token'] = $this->embedApi->getEmbedToken($quote_total, $currency, $buyer_id);
        $result['amount'] = $quote_total;
        $result['buyer_id'] = $buyer_id;

        return $result;
    }

    /**
     * retrieve fully loaded quote model to interact with Quote Properly
     *
     * @param string
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuoteModel($cartId)
    {
        $cartId = $this->gr4vyHelper->getQuoteIdFromMask($cartId);
        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->getCartRepository();

        /** @var \Magento\Quote\Model\Quote $quote */
        return $quoteRepository->getActive($cartId);
    }

    /**
     * retrieve shipping_address for current quote, return null if there is no shipping address (virtual or downloadable products)
     *
     * @return Magento\Quote\Model\Quote\Address|null
     */
    private function getShippingAddress($quote)
    {
        if ($quote->getShippingAddress()) {
            return $quote->getShippingAddress();
        }

        return null;
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
     * retrieve buyer buy gr4vy transaction using gr4vy_transaction_id
     *
     * @param string
     * @return Gr4vy\Magento\Api\Data\TransactionInterface
     */
    public function getByGr4vyTransactionId($gr4vy_transaction_id)
    {
        $transactionSearchCriteria = $this->searchCriteriaBuilder->addFilter('gr4vy_transaction_id', $gr4vy_transaction_id, 'eq')->create();
        $transactionSearchResults = $this->getList($transactionSearchCriteria);

        if ($transactionSearchResults->getTotalCount() > 0) {
            list($item) = $transactionSearchResults->getItems();
            return $item;
        }

        return null;
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
            \Gr4vy\Magento\Api\Data\TransactionInterface::class
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
        \Gr4vy\Magento\Api\Data\TransactionInterface $transaction
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

