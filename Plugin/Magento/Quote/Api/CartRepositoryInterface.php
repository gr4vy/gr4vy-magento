<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Plugin\Magento\Quote\Api;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Gr4vy\Payment\Api\BuyerRepositoryInterface;
use Gr4vy\Payment\Api\Data\BuyerInterface as DataBuyerInterface;
use Gr4vy\Payment\Model\Client\Buyer as Gr4vyBuyer;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;
use Magento\Quote\Model\QuoteRepository;

class CartRepositoryInterface
{
    /**
     * @var searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

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
        SearchCriteriaBuilder $searchCriteriaBuilder,
        QuoteRepository $quoteRepository,
        BuyerRepositoryInterface $buyerRepository,
        DataBuyerInterface $buyerData,
        Gr4vyBuyer $buyerApi,
        Gr4vyLogger $gr4vyLogger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        // try to load quote after initialized
        try {
            $quoteModel = $subject->get($quote->getId());
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // if quote object not initialized, set quoteModel to null to prevent add to cart error
            $this->gr4vyLogger->logException($e);
            $quoteModel = null;
        }

        if (empty($quoteModel) || empty($quoteModel->getData('gr4vy_buyer_id'))) {
            $external_identifier = $quote->getCustomerId();
            $buyer_model = $this->getBuyerByExternalIdentifier($external_identifier);

            if ($buyer_model) {
                // check for customer in gr4vy_buyers table
                $quote->setData('gr4vy_buyer_id',$buyer_model->getBuyerId());
            }
            else {
                // if customer not logged in, create anonymous buyer without external_identifier and display_name
                $display_name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();
                $buyer_id = $this->buyerApi->createBuyer($external_identifier, $display_name);
                if ($buyer_id == Gr4vyBuyer::ERROR_DUPLICATE) {
                    $buyer = $this->buyerApi->getBuyer($external_identifier);
                    $buyer_id = $buyer->getId();
                }
                // assign buyer_id to quote object
                $quote->setData('gr4vy_buyer_id', $buyer_id);

                // save buyer to gr4vy_buyers table if external_identifier & display_name defined
                $this->saveBuyerData($external_identifier, $display_name, $buyer_id);
            }
        }

        $result = $proceed($quote);
    }

    /**
     * retrieve buyer buy external_identifier
     *
     * @param string
     * @return Gr4vy\Payment\Model\Buyer
     */
    public function getBuyerByExternalIdentifier($external_identifier)
    {
        if (!empty($external_identifier)) {
            $buyerSearchCriteria = $this->searchCriteriaBuilder->addFilter('external_identifier', $external_identifier, 'eq')->create();
            $buyerSearchResults = $this->buyerRepository->getList($buyerSearchCriteria);

            if ($buyerSearchResults->getTotalCount() > 0) {
                list($item) = $buyerSearchResults->getItems();
                return $item;
            }
        }

        return null;
    }

    /**
     * save buyer
     *
     * @param string
     * @param string
     * @param string
     * @return BuyerModel
     */
    public function saveBuyerData($external_identifier, $display_name, $buyer_id)
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

