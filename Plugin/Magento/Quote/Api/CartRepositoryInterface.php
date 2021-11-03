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
        Gr4vyLogger $gr4vyLogger
    ) {
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->buyerRepository = $buyerRepository;
        $this->buyerData = $buyerData;
        $this->buyerApi = $buyerApi;
        $this->gr4vyLogger = $gr4vyLogger;
    }

    /**
     * whenever cart is saved, interact with gr4vy payment
     *
     * @return void
     */
    public function aroundSave(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $quote_id = $quote->getId();
        $customer = $this->customerSession->getCustomer();
        $external_identifier = $customer->getId();

        if ($external_identifier) {
            if ($buyerModel = $this->buyerRepository->getByExternalIdentifier($external_identifier)) {
                // case 1: customer logged in & associated with gr4vy for current merchant account
                $quote->setData('gr4vy_buyer_id', $buyerModel->getBuyerId());
            }
            else {
                // case 2: customer logged in & not associated with gr4vy
                $display_name = $customer->getFirstname() . " " . $customer->getLastname();
                $buyer_id = $this->getGr4vyBuyerId($external_identifier, $display_name);

                // assign buyer_id to quote object
                $quote->setData('gr4vy_buyer_id', $buyer_id);

                // save buyer to gr4vy_buyers table if external_identifier & display_name defined
                $this->saveBuyerData($external_identifier, $display_name, $buyer_id);
            }
        }
        else {
            if (!$quote->getData('gr4vy_buyer_id')) {
                // case 3: customer not logged in - anonymous gr4vy buyer id
                $display_name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();
                $buyer_id = $this->getGr4vyBuyerId($external_identifier, $display_name);

                $quote->setData('gr4vy_buyer_id', $buyer_id);
            }
        }

        $result = $proceed($quote);

        return $result;
    }

    /**
     * retrieve gr4vy buyer id
     *
     * @param string
     * @param string
     * @return string
     */
    protected function getGr4vyBuyerId($external_identifier, $display_name)
    {
        $buyer_id = $this->buyerApi->createBuyer($external_identifier, $display_name);

        if ($buyer_id == Gr4vyBuyer::ERROR_DUPLICATE) {
            $buyer = $this->buyerApi->getBuyer($external_identifier);
            $buyer_id = $buyer->getId();
        }

        return $buyer_id;
    }

    /**
     * save buyer
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

