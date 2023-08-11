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
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManagerInterface;
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
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Customer
     */
    private $customer = null;

    /**
     * @var CustomerSession
     */
    protected $visitorSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        BuyerRepositoryInterface $buyerRepository,
        DataBuyerInterface $buyerData,
        Gr4vyBuyer $buyerApi,
        Logger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper,
        SessionManagerInterface $visitorSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->buyerRepository = $buyerRepository;
        $this->buyerData = $buyerData;
        $this->buyerApi = $buyerApi;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->visitorSession = $visitorSession;
    }

    /**
     * initialize gr4vy customer data in session. if customer not in gr4vy, create new record 
     *
     * @param Magento\Quote\Model\Quote $quote
     * @return void
     */
    public function connectQuoteWithGr4vy($quote)
    {
        try {
            $quote->setData('gr4vy_buyer_id', $this->getGr4vyBuyerId($quote));
            $this->updateGr4vyBuyerAddressFromQuote($quote);
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

    /**
     * initialize gr4vy customer data. if customer not in gr4vy, create new record 
     *
     * covered scenarios
     * 1. guest - no gr4vy_buyer_id and external_identifier stored in session
     * 2. guest - gr4vy_buyer_id stored in quote
     * 3. logged in - no gr4vy_buyer_id and external_identifier stored in session
     * 4. logged in - gr4vy_buyer_id previously stored in session (create account on checkout page)
     * 5. logged in - buyer_id not available due to unexpected issue like missing private_key
     *
     * @param Magento\Quote\Model\Quote $quote
     * @return void
     */
    public function getGr4vyBuyerId($quote = null)
    {
        if ($customer = $this->getCurrentCustomer()) {
            $external_identifier = $customer->getId();
            $display_name = $customer->getFirstname() . " " . $customer->getLastname();
        }

        if (!$this->customerSession->isLoggedIn()) {
            // Customer is not logged in
            // This can only happen when called from connectQuoteWithGr4vy
            
            $customerFirstname = $quote->getShippingAddress()->getData("firstname");
            $customerLastname = $quote->getShippingAddress()->getData("lastname");
            $display_name = $customerFirstname . " " . $customerLastname;
            $visitor = $this->visitorSession->getVisitorData();
            //we prefix external_identifer with visitor_ so it doesn't clash with customer ID
            $external_identifier = "visitor_" . $visitor["visitor_id"];
            $quote->setCustomerFirstname($customerFirstname);
            $quote->setCustomerLastname($customerLastname);
        }

        $buyerModel = $this->buyerRepository->getByExternalIdentifier($external_identifier, $this->gr4vyHelper->getGr4vyId());

        if (!is_object($buyerModel)) {
            $gr4vy_buyer_id = $this->createGr4vyBuyer($external_identifier, $display_name);

            if ($external_identifier) {
                $this->saveBuyerData($external_identifier, $display_name, $gr4vy_buyer_id);
            }
        }
        elseif (!$buyerModel->getBuyerId()) {
            // if buyer_id is not empty - due to unknown
            $gr4vy_buyer_id = $this->createGr4vyBuyer($external_identifier, $display_name);

            $buyerModel->setBuyerId($gr4vy_buyer_id);
            $this->buyerRepository->save($buyerModel);
        }
        else {
            $gr4vy_buyer_id = $buyerModel->getBuyerId();
        }

        $this->gr4vyLogger->logMixed(["buyer_id"=>$gr4vy_buyer_id], "returning in getGr4vyBuyerId");
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
        $buyerModel = $this->buyerRepository->getByExternalIdentifier($customer->getId(), $this->gr4vyHelper->getGr4vyId());

        if ($buyerModel && ($default_billing = $customer->getDefaultBillingAddress())) {
            $street = $default_billing->getStreet();
            $street2 = null;
            if (is_array($street)) {
                if (count($street) > 1) {
                    $street2 = $street[1];
                }
                $street = $street[0];
            }
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
                    "street" => $street,
                    "street2" => $street2,
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
     * update gr4vy buyer billing address from $quote
     *
     * @param Magento\Quote\Model\Quote $quote
     * @return void
     */
    public function updateGr4vyBuyerAddressFromQuote($quote)
    {
        $external_identifier = null;
        $customer = $this->getCurrentCustomer();

        if (!$this->customerSession->isLoggedIn()) {
            $visitor = $this->visitorSession->getVisitorData();
            //we prefix external_identifer with visitor_ so it doesn't clash with customer ID
            $external_identifier = "visitor_" . $visitor["visitor_id"];
        }
        else {
            $external_identifier = $customer->getId();
        }
        
        $buyerModel = $this->buyerRepository->getByExternalIdentifier($external_identifier, $this->gr4vyHelper->getGr4vyId());

        $billing_details = null;

        $billingAddress = $quote->getBillingAddress();

        if ($billingAddress) {
            $billing_details = [
                "first_name" => $quote->getCustomerFirstname(),
                "last_name" => $quote->getCustomerLastname(),
                "email_address" => $quote->getCustomerEmail()
            ];

            $phone = $billingAddress->getData('telephone');
            if ($phone) {
                $billing_details["phone_number"] = $phone;
            }
            $city = $billingAddress->getData('city');
            if ($city) {
                $street = $billingAddress->getData('street');
                $street2 = null;
                if(strstr($street, "\n")) {
                    $streetArr = explode("\n", $street);
                    $street = $streetArr[0];
                    if (count($streetArr) > 1) {
                        $street2 = $streetArr[1];
                    }
                }

                $billing_details["address"] = [
                    "city" => $city,
                    "country" => $billingAddress->getData('country_id'),
                    "postal_code" => $billingAddress->getData('postcode'),
                    "state" => $billingAddress->getData('region'),
                    "street" => $street,
                    "street2" => $street2,
                    "organization" => ""
                ];
            }
        }
        

        if ($buyerModel && $billing_details) {
            if ($buyerModel->getBillingAddress() != json_encode($billing_details)) {
                $this->gr4vyLogger->logMixed(["billing_details"=>$billing_details], "billing address has changed");
                $this->updateGr4vyBuyer($buyerModel->getBuyerId(), $billing_details);

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
            $buyer = $this->buyerApi->updateBuyer($buyer_id, $billing_details);
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
                $buyer_id = $buyer["id"];
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
             ->setGr4vyId($this->gr4vyHelper->getGr4vyId())
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
