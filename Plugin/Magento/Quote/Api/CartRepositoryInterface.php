<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Quote\Api;

use Gr4vy\Magento\Api\BuyerRepositoryInterface;
use Gr4vy\Magento\Api\Data\BuyerInterface as DataBuyerInterface;
use Gr4vy\Magento\Model\Client\Buyer as Gr4vyBuyer;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Magento\Quote\Model\QuoteRepository;
use Magento\Customer\Model\Session as CustomerSession;

class CartRepositoryInterface
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

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
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        CustomerSession $customerSession,
        QuoteRepository $quoteRepository,
        BuyerRepositoryInterface $buyerRepository,
        DataBuyerInterface $buyerData,
        Gr4vyBuyer $buyerApi,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger
    ) {
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->buyerRepository = $buyerRepository;
        $this->buyerData = $buyerData;
        $this->buyerApi = $buyerApi;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
    }

    /**
     * whenever cart is saved, interact with gr4vy payment
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function aroundSave(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            $quote->setData('gr4vy_buyer_id', $this->getGr4vyBuyerId($quote));
        }

        $result = $proceed($quote);

        return $result;
    }

    /**
     * initialize gr4vy customer data in session. if customer not in gr4vy, create new record 
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    protected function getGr4vyBuyerId($quote)
    {
        $customer = $this->customerSession->getCustomer();
        $external_identifier = $customer->getId();
        $buyerModel = $this->buyerRepository->getByExternalIdentifier($external_identifier);

        // covered scenarios
        // 1. guest - no gr4vy_buyer_id and external_identifier stored in session
        // 2. guest - gr4vy_buyer_id stored in session
        // 3. logged in - no gr4vy_buyer_id and external_identifier stored in session
        // 3. logged in - gr4vy_buyer_id previously stored in session (create account on checkout page)
        if ((!$external_identifier && !$this->customerSession->getGr4vyBuyerId())
            || ($this->customerSession->getExternalIdentifier() != $external_identifier)) {
            list($createBuyer, $gr4vy_buyer_id) = $this->verifyBuyerModel($buyerModel, $this->customerSession->getGr4vyBuyerId());

            if ($createBuyer) {
                if ($customer->getFirstname()) {
                    $display_name = $customer->getFirstname() . " " . $customer->getLastname();
                }
                else {
                    $display_name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();
                }

                $gr4vy_buyer_id = $this->createGr4vyBuyer($external_identifier, $display_name);
                $this->saveBuyerData($external_identifier, $display_name, $gr4vy_buyer_id);
            }

            $this->customerSession->setExternalIdentifier($external_identifier);
            $this->customerSession->setGr4vyBuyerId($gr4vy_buyer_id);
        }

        return $this->customerSession->getGr4vyBuyerId();
    }

    /**
     * make sure buyer model is valid. determine createBuyer action
     *
     * @param DataBuyerInterface
     * @return array [createBuyer, gr4vy_buyer_id]
     */
    protected function verifyBuyerModel($buyerModel, $buyer_id = null)
    {
        $result = [false, $buyer_id];
        if (empty($buyerModel)) {
            $result[0] = true;
        }
        else {
            $result[1] = $buyerModel->getBuyerId();
            if ($verifier = $this->buyerApi->getBuyer($buyerModel->getExternalIdentifier())) {
                if ($buyerModel->getBuyerId() != $verifier->getId()) {
                    // remove faulty record
                    $this->buyerRepository->delete($buyerModel);
                    $result[0] = true;
                }
            }
        }

        return $result;
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

