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
use Gr4vy\Magento\Helper\Customer as CustomerHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Locale\Resolver;
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
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var Gr4vyEmbed
     */
    protected $embedApi;

    /**
     * @var Resolver
     */
    private $resolver;

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
     * @var categoryRepository
     */
    private $categoryRepository;

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
     * @param CustomerHelper $customerHelper
     * @param Gr4vyEmbed $embedApi
     * @param Resolver $resolver
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
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
        CustomerHelper $customerHelper,
        Gr4vyEmbed $embedApi,
        Resolver $resolver,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
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
        $this->customerHelper = $customerHelper;
        $this->embedApi = $embedApi;
        $this->resolver = $resolver;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryRepository = $categoryRepository;
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
        if (!$quote->getData('gr4vy_buyer_id')) {
            $this->customerHelper->connectQuoteWithGr4vy($quote);
            $quote->save();
        }
        else {
            $this->customerHelper->updateGr4vyBuyerAddressFromQuote($quote);
        }

        $buyer_id = $quote->getData('gr4vy_buyer_id');

        // NOTE: $quote->getGrandTotal after shipping method specified contains calculated shipping amount
        $quote_total = $quote->getGrandTotal();

        $result = [];
        $result['token'] = $this->embedApi->getEmbedToken($quote_total, $currency, $buyer_id);
        $result['amount'] = $this->round_number($quote_total);
        $result['buyer_id'] = $buyer_id;
        $result['items'] = $this->getCartItemsData($quote, $result['amount']);
        $result['locale'] = $this->getLocaleCode();

        $this->gr4vyLogger->logMixed(["result"=>$result], "getEmbedToken");

        return $result;
    }

    /**
     * Get the current locale code
     *
     * @return string
     */
    public function getLocaleCode(): string
    {
        $result = $this->resolver->getLocale();
        if ($result === null) {
            return '';
        }
        $parts = explode('_', $result);
        $result = $parts[0].'-'.strtoupper($parts[1]);

        return $result;
    }

    /**
     * multiply by 100 and round input number
     *
     * @param float
     * @return integer
     */
    public function round_number($input)
    {
        return round(floatval($input) * 100);
    }

    /**
     * NOTE: allowed product types are
     * 'physical', 'discount', 'shipping_fee', 'sales_tax', 'digital', 'gift_card', 'store_credit', 'surcharge'
     *
     * @param Magento\Quote\Model\Quote
     * @param integer
     * @return Array
     */
    public function getCartItemsData($quote, $totalAmount)
    {
        $items = [];
        $itemsTotal = 0;
        foreach ($quote->getAllVisibleItems() as $item){
            $product = $item->getProduct();
            $categories = $product->getCategoryIds();

            $gr4vyCategories = [];
            foreach ($categories as $categoryId) {
                $category = $this->categoryRepository->get($categoryId, $quote->getStore()->getId());
                if ($category) {
                    $gr4vyCategories[] = $category->getName();    
                }
            }
            
            $productUrl = $product->getUrlModel()->getUrl($product);
            $itemAmount = $this->round_number($item->getPriceInclTax());
            $itemsTotal += $itemAmount;
            $items[] = [
                'name' => $item->getName(),
                'quantity' => $item->getQty(),
                'unitAmount' => $itemAmount,
                'sku' => $item->getSku(),
                'productUrl' => $productUrl,
                'productType' => 'physical',
                'categories' => $gr4vyCategories
            ];
        }

        // calculate shipping fee as cart item
        $shipping_address = $quote->getShippingAddress();
        $shippingAmount = $this->round_number($shipping_address->getShippingInclTax());
        $itemsTotal += $shippingAmount;
        $items[] = [
            'name' => $shipping_address->getShippingMethod(),
            'quantity' => 1,
            'unitAmount' => $shippingAmount,
            'sku' => $shipping_address->getShippingMethod(),
            'productUrl' => $quote->getStore()->getUrl(),
            'productType' => 'shipping_fee',
            'categories' => ['shipping']
        ];

        if ($totalAmount != $itemsTotal) {
            return [];
        }

        return $items;
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

