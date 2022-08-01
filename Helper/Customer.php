<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Gr4vy\Magento\Api\BuyerRepositoryInterface;
use Gr4vy\Magento\Api\Data\BuyerInterface as DataBuyerInterface;
use Gr4vy\Magento\Model\Client\Buyer as Gr4vyBuyer;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Customer extends AbstractHelper
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var BuyerRepositoryInterface
     */
    protected $buyerRepository;

    /**
     * @var DataBuyerInterface
     */
    protected $buyerData;

    /**
     * @var Gr4vybuyer
     */
    protected $buyerApi;

    /**
     * @var Logger
     */
    protected $gr4vyLogger;

    /**
     * @var Customer
     */
    private $customer = null;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerHelper $customerHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        BuyerRepositoryInterface $buyerRepository,
        DataBuyerInterface $buyerData,
        Gr4vyBuyer $buyerApi,
        Logger $gr4vyLogger,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->buyerRepository = $buyerRepository;
        $this->buyerData = $buyerData;
        $this->buyerApi = $buyerApi;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * initialize gr4vy customer data in session. if customer not in gr4vy, create new record 
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function connectQuoteWithGr4vy($quote)
    {
        $display_name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();

        try {
            $quote->setData('gr4vy_buyer_id', $this->getGr4vyBuyerId($display_name));
            $this->updateGr4vyBuyerAddress();
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * retrieve current customer from session
     *
     * @return customer
     */
    public function getCurrentCustomer()
    {
        if ($this->customer === null) {
            $customer = $this->customerSession->getCustomer();

            $this->customer = $customer;
        }

        return $this->customer;
    }

    /**
     * check created / updated Magento Customer address. if it's default billing address, update Gr4vy Customer Address Record
     *
     * @param \Magento\Customer\Api\Data\AddressInterface
     * @return void
     */
    public function checkGr4vyAddress(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        try {
            $this->updateGr4vyBuyerAddress($address);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    public function getGr4vyId()
    {
        return (string) $this->scopeConfig->getValue(\Gr4vy\Magento\Helper\Data::GR4VY_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * initialize gr4vy customer data. if customer not in gr4vy, create new record 
     *
     * covered scenarios
     * 1. guest - no gr4vy_buyer_id and external_identifier stored in session
     * 2. guest - gr4vy_buyer_id stored in session
     * 3. logged in - no gr4vy_buyer_id and external_identifier stored in session
     * 3. logged in - gr4vy_buyer_id previously stored in session (create account on checkout page)
     *
     * @param string $display_name
     * @param string $external_identifier
     * @return void
     */
    public function getGr4vyBuyerId($display_name = null, $external_identifier = null)
    {
        if ($customer = $this->getCurrentCustomer()) {
            $external_identifier = $customer->getId();
            $display_name = $customer->getFirstname() . " " . $customer->getLastname();
        }

        $buyerModel = $this->buyerRepository->getByExternalIdentifier($external_identifier, $this->getGr4vyId());

        if (is_null($buyerModel)) {
            $gr4vy_buyer_id = $this->createGr4vyBuyer($external_identifier, $display_name);

            if ($external_identifier) {
                $this->saveBuyerData($external_identifier, $display_name, $gr4vy_buyer_id);
            }
        }
        else {
            $gr4vy_buyer_id = $buyerModel->getBuyerId();
        }

        return $gr4vy_buyer_id;
    }

    /**
     * update gr4vy buyer billing address
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    public function updateGr4vyBuyerAddress($address = null)
    {
        $customer = $this->getCurrentCustomer();
        $gr4vy_buyer_id = $this->getGr4vyBuyerId();
        $buyerModel = $this->buyerRepository->getByExternalIdentifier($customer->getId(), $this->getGr4vyId());

        if ($buyerModel && ($default_billing = $customer->getDefaultBillingAddress())) {
            $billing_details = [
                "first_name" => $default_billing->getFirstname(),
                "last_name" => $default_billing->getLastname(),
                "email_address" => $customer->getEmail(),
                "phone_number" => $default_billing->getTelephone(),
                "address" => [
                    "city" => $default_billing->getCity(),
                    "country" => $default_billing->getCountryId(),
                    "postal_code" => $default_billing->getPostcode(),
                    "state" => $default_billing->getRegion(),
                    "street" => $default_billing->getStreet(),
                    "organization" => $default_billing->getCompany()
                ]
            ];

            if ($buyerModel->getBillingAddress() != json_encode($billing_details)) {
                $this->updateGr4vyBuyer($gr4vy_buyer_id, $billing_details);

                // save billing_details to gr4vy_buyers table
                $this->buyerRepository->save($buyerModel->setBillingAddress(json_encode($billing_details)));
            }
        }
    }

    /**
     * update gr4vy buyer with billing address detail
     *
     * @param string $buyer_id
     * @param array $billing_details
     *
     * @return void
     */
    protected function updateGr4vyBuyer($buyer_id, $billing_details)
    {
        if ($buyer_id) {
            $buyer_id = $this->buyerApi->updateBuyer($buyer_id, $billing_details);
        }

        return $buyer_id;
    }

    /**
     * create then retrieve gr4vy buyer id
     *
     * @param string
     * @param string
     * @return string
     */
    protected function createGr4vyBuyer($external_identifier, $display_name)
    {
        $buyer_id = $this->buyerApi->createBuyer($external_identifier, $display_name);

        if ($buyer_id == Gr4vyBuyer::ERROR_DUPLICATE) {
            if ($buyer = $this->buyerApi->getBuyer($external_identifier)) {
                $buyer_id = $buyer->getId();
            }
        }

        return $buyer_id;
    }

    /**
     * save buyer - only applicable if customer is logged in , so $external_identifier and $display_name are not empty
     *
     * @param string
     * @param string
     * @param string
     * @return BuyerModel
     */
    protected function saveBuyerData($external_identifier, $display_name, $buyer_id)
    {
        if (empty($external_identifier) || empty($display_name)) {
            return false;
        }

        $this->buyerData
             ->setGr4vyId($this->getGr4vyId())
             ->setExternalIdentifier($external_identifier)
             ->setDisplayName($display_name)
             ->setBuyerId($buyer_id);

        try {
            return $this->buyerRepository->save($this->buyerData);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }
}
