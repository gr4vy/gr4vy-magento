<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Gr4vy\Magento\Model\Payment\Gr4vy;
use Gr4vy\Magento\Api\Data\TransactionInterfaceFactory;
use Gr4vy\Magento\Api\Data\TransactionSearchResultsInterfaceFactory;
use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Gr4vy\Magento\Model\ResourceModel\Transaction as ResourceTransaction;
use Gr4vy\Magento\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Order as OrderHelper;
use Gr4vy\Magento\Model\Client\Transaction as Gr4vyTransaction;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Session\SessionManagerInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    const ORDER_STATUS_PENDING = 'pending';

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

    private $orderManagement;

    protected $quoteManagement;

    protected $session;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Gr4vyTransaction
     */
    public $transactionApi;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    protected $orderPaymentRepository;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var OrderFactory
     */
    private $orderFactory;


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
     * @param JsonFactory $resultJsonFactory
     * @param SessionManagerInterface $session
     * @param Gr4vyLogger $gr4vyLogger
     * @param Gr4vyHelper $gr4vyHelper
     * @param Gr4vyTransaction $transactionApi
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param OrderHelper $orderHelper
     * @param OrderFactory $orderFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderManagementInterface $orderManagement
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
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $session,
        Gr4vyLogger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyTransaction $transactionApi,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        OrderHelper $orderHelper,
        OrderFactory $orderFactory,
        QuoteManagement $quoteManagement,
        OrderManagementInterface $orderManagement
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
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->transactionApi = $transactionApi;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderHelper = $orderHelper;
        $this->orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->quoteManagement = $quoteManagement;
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
    public function lockBasket($cartId) {
        $originalQuote = $this->getQuoteModel($cartId);

        $newQuote = $this->quoteFactory->create();
        $newQuote->setStoreId($originalQuote->getStoreId());
        $newQuote->setCustomer($originalQuote->getCustomer());
        $newQuote->setCurrency($originalQuote->getCurrency());

        foreach ($originalQuote->getAllVisibleItems() as $item) {
            $newItem = clone $item;
            $newQuote->addItem($newItem);
        }
        
        $newQuote->collectTotals();
        $this->cartRepository->save($newQuote);

            $this->session->setData('locked_cart_id', $newQuote->getId());
        $customValue = $this->session->getData('locked_cart_id');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function processTransaction(
        $cartId,
        $transactionId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Gr4vy\Magento\Api\Data\MethodInterface $methodData,
        \Gr4vy\Magento\Api\Data\ServiceInterface $serviceData,
        \Gr4vy\Magento\Api\Data\TransactionInterface $transactionData
    ) {
        $this->restoreBasket($cartId);
        $transaction = $this->transactionApi->getTransactionDetail($transactionId);
        $this->parseGr4vyRawData($paymentMethod, $methodData, $serviceData, $transactionData, $transaction);
        $this->setPaymentInformation($cartId, $paymentMethod, $methodData, $serviceData, $transactionData);
    
        $statuses = $this->orderHelper->getGr4vyTransactionStatuses();

        if (in_array($transactionData->getStatus(), $statuses['cancel'])) {
            $result = $this->saveFailedOrder($cartId, $paymentMethod, $methodData, $serviceData, $transactionData);
            return json_encode($result);
        } else if (
            in_array($transactionData->getStatus(), $statuses['processing']) ||
            in_array($transactionData->getStatus(), $statuses['success']) 
        ){
            $result = [];
            $result['success'] = true;
            return json_encode($result);
        }
    
        $result = [];
        $result['success'] = false;
        return json_encode($result);
    }

    private function restoreBasket($cartId) {
        $originalQuote = $this->getQuoteModel($cartId);
        $sessionCartId = $this->session->getData('locked_cart_id');
        $sessionQuote = $this->getQuoteModel($sessionCartId);

        $originalQuote->setStoreId($sessionQuote->getStoreId());
        $originalQuote->setCustomer($sessionQuote->getCustomer());
        $originalQuote->setCurrency($sessionQuote->getCurrency());

        foreach ($originalQuote->getAllItems() as $item) {
            $originalQuote->removeItem($item->getItemId());
        }

        foreach ($sessionQuote->getAllVisibleItems() as $item) {
            $newItem = clone $item;
            $originalQuote->addItem($newItem);
        }
        
        $originalQuote->collectTotals();
        $this->cartRepository->save($originalQuote);
    }

    /**
     * Set the PaymentMethod Data with the data received from Gr4vy
     *
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Gr4vy\Magento\Api\Data\MethodInterface $methodData
     * @param \Gr4vy\Magento\Api\Data\ServiceInterface $serviceData
     * @param \Gr4vy\Magento\Api\Data\TransactionInterface $transactionData
     * @param object $transactionRawData
     */

    private function parseGr4vyRawData($paymentMethod, $methodData, $serviceData, $transactionData, $transactionRawData) {
        $paymentMethodRawData = $transactionRawData["payment_method"];
        $paymentServiceRawData = $transactionRawData["payment_service"];

        $paymentMethod->setMethod("gr4vy");

        $paymentMethodType = "";
        $paymentMethodExpYear = "";
        $paymentMethodExpMonth = "";
        $paymentMethodLast4 = "";

        if ($paymentMethodRawData["scheme"] != null) {
            $paymentMethodType = $paymentMethodRawData["scheme"];
            $methodData->setScheme($paymentMethodRawData["scheme"]);
        } else if ($paymentMethodRawData["method"] != null){
            $paymentMethodType = $paymentMethodRawData["method"];
        }

        if ($paymentMethodRawData["expiration_date"] != null) {
            $paymentMethodExpYear = substr($paymentMethodRawData["expiration_date"], -2);
            $paymentMethodExpMonth = substr($paymentMethodRawData["expiration_date"], 0, 2);
            $methodData->setExpirationDate($paymentMethodRawData["expiration_date"]);
        }

        if ($paymentMethodRawData["label"] != null) {
            $paymentMethodLast4 = $paymentMethodRawData["label"];
            $methodData->setLabel($paymentMethodRawData["label"]);
        }

        $additionalData = (object)[
            "cc_type" => $paymentMethodType,
            "cc_exp_year" => $paymentMethodExpYear,
            "cc_exp_month" => $paymentMethodExpMonth,
            "cc_last_4" => $paymentMethodLast4
        ];
        $paymentMethod->setAdditionalData(json_encode($additionalData));

        if ($paymentMethodRawData["id"] != null) {
            $methodData->setMethodId($paymentMethodRawData["id"]);
        }

        if ($paymentMethodRawData["method"] != null){
            $methodData->setMethod($paymentMethodRawData["method"]);
        } 

        if ($paymentMethodRawData["external_identifier"] != null){
            $methodData->setExternalIdentifier($paymentMethodRawData["external_identifier"]);
        } 

        if ($paymentMethodRawData["approval_url"] != null){
            $methodData->setApprovalUrl($paymentMethodRawData["approval_url"]);
        }

        if ($paymentServiceRawData != null) {
            $serviceData->setServiceId($paymentServiceRawData["id"]);
            $serviceData->setMethod($paymentServiceRawData["method"]);
            $serviceData->setPaymentServiceDefinitionId($paymentServiceRawData["payment_service_definition_id"]);
            $serviceData->setDisplayName($paymentServiceRawData["display_name"]);
        }

        $transactionData->setStatus($transactionRawData["status"]);
        $transactionData->setAmount($transactionRawData["amount"]);
        $transactionData->setCapturedAmount($transactionRawData["captured_amount"]);
        $transactionData->setRefundedAmount($transactionRawData["refunded_amount"]);
        $transactionData->setCurrency($transactionRawData["currency"]);
        $transactionData->setGr4vyTransactionId($transactionRawData["id"]);
        $transactionData->setMethodId($methodData->getMethodId());
        $transactionData->setServiceId($serviceData->getServiceId());
        if ($transactionRawData["buyer"] != null) {
            if ($transactionRawData["buyer"]["id"]) {
                $transactionData->setBuyerId($transactionRawData["buyer"]["id"]);
            }
        }
    }

    private function saveFailedOrder(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Gr4vy\Magento\Api\Data\MethodInterface $methodData,
        \Gr4vy\Magento\Api\Data\ServiceInterface $serviceData,
        \Gr4vy\Magento\Api\Data\TransactionInterface $transactionData
    )
    {
        $this->gr4vyLogger->logMixed(['Going to save a failed tx as a cancelled Magento order.']);

        try {
            // Load the quote
            $quote = $this->getQuoteModel($cartId);
            $payment = $quote->getPayment();
            $payment->setCcLast4($methodData->getLabel());
            $additionalData = $paymentMethod->getAdditionalData();
            $payment->setCcType($additionalData["cc_type"]);
            $payment->setCcExpMonth($additionalData["cc_exp_month"]);
            $payment->setCcExpYear($additionalData["cc_exp_year"]);
            $gr4vy_transaction_id = $transactionData->getGr4vyTransactionId();
            $payment->setData('gr4vy_transaction_id', $gr4vy_transaction_id)->save();
            
            // Saving the actual customer email 
            $customerEmail = $quote->getCustomerEmail();
            // Changing the customer email to a fake email to avoid sending order confirmation email for this order
            $quote->setCustomerEmail("fake@email.com");
            $quote->save();

            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);

            if ($order) {
                $this->orderManagement->cancel($order->getId());
                // Setting the customer email back to the original value for both the quote and the stored order
                $quote->setCustomerEmail($customerEmail);
                $order->setCustomerEmail($customerEmail);
                $quote->save();
                $order->save();

                $result = [];
                $result['success'] = false;
                return $result;
            } else {
                $this->gr4vyLogger->logMixed(['Failed to place an order! Please Try again.']);
                $result = [];
                $result['success'] = false;
                $result['error_message'] = "Failed to place an order! Please Try again.";
                return $result;
            }
        } catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
            $result = [];
            $result['success'] = false;
            $result['error_message'] = "Failed to place an order! Please Try again.";
            return $result;
        }
    }

    /**
     * Set Payment Information - Associate transaction payment detail with magento payment object
     * @param string
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Gr4vy\Magento\Api\Data\MethodInterface $methodData
     * @param \Gr4vy\Magento\Api\Data\ServiceInterface $serviceData
     * @param \Gr4vy\Magento\Api\Data\TransactionInterface $transactionData
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
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
        $payment->setCcLast4($methodData->getLabel());
        $payment->setCcType($methodData->getScheme());
        $expiryDate = $methodData->getExpirationDate();
        if ($expiryDate) {
        $expiry = explode("/", $expiryDate);
            $payment->setCcExpMonth($expiry[0]);
            $payment->setCcExpYear($expiry[1]);
        }
        // $payment->setCcAvsStatus();
        $payment->setData('gr4vy_transaction_id', $transactionData->getGr4vyTransactionId())->save();

        $this->gr4vyLogger->logMixed($payment->getData());

        $quote_payment_id = $this->paymentMethodManagement->set($cartId, $paymentMethod);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setGuestEmail($cartId, $email)
    {
        $quote = $this->getQuoteModel($cartId);
        $quote->setCustomerEmail($email);
        $quote->save();

        return true;
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
        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->getCartRepository();

        /** @var \Magento\Quote\Model\Quote $quote */
        return $quoteRepository->getActive($cartId);
    }

    /**
     * Get Cart repository
     *
     * @return CartRepositoryInterface
     * @deprecated 100.2.0
     */
    private function getCartRepository()
    {
        if (!$this->cartRepository) {
            $this->cartRepository = ObjectManager::getInstance()
                ->get(CartRepositoryInterface::class);
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

    /**
     * @param $orderIncrementId
     */
    public function getOrderByIncrementId($orderIncrementId){
        return $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
    }

    public function updateOrderPayment($order, $gr4vy_transaction_id)
    {
        /** @var Payment $payment */
        $orderPayment = $order->getPayment();
        $orderPayment->setData('gr4vy_transaction_id', $gr4vy_transaction_id);
        $orderPayment->setData('transaction_id', $gr4vy_transaction_id);
        $orderPayment->setData('last_trans_id', $gr4vy_transaction_id);
        $this->orderPaymentRepository->save($orderPayment);
    }
}

