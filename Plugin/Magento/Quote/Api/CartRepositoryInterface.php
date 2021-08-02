<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Plugin\Magento\Quote\Api;

use Gr4vy\Payment\Api\BuyerRepositoryInterface;
use Gr4vy\Payment\Api\Data\BuyerInterface as DataBuyerInterface;
use Gr4vy\Payment\Model\Client\Buyer as Gr4vyBuyer;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;
use Magento\Quote\Model\QuoteRepository;

class CartRepositoryInterface
{
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
    protected $gr4vy_logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        BuyerRepositoryInterface $buyerRepository,
        DataBuyerInterface $buyerData,
        Gr4vyBuyer $buyerApi,
        Gr4vyLogger $gr4vy_logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->buyerRepository = $buyerRepository;
        $this->buyerData = $buyerData;
        $this->buyerApi = $buyerApi;
        $this->gr4vy_logger = $gr4vy_logger;
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
            $this->gr4vy_logger->logException($e);
            $quoteModel = null;
        }

        if (empty($quoteModel) || empty($quoteModel->getData('gr4vy_buyer_id'))) {
            $external_buyer_id = $quote->getCustomerId();
            try {
                $gr4vy_buyer_id = $this->buyerRepository->get($external_buyer_id)->getBuyerId();
            }
            catch (\Exception $e) {
                // if no record, set to null
                $gr4vy_buyer_id = null;
            }

            if (!empty($gr4vy_buyer_id)) {
                // check for customer in gr4vy_buyers table
                $quote->setGr4vyBuyerId($gr4vy_buyer_id);
            }
            else {
                // if customer not logged in, create anonymous buyer without external_identifier and display_name
                $external_identifier = $quote->getCustomerId();
                $display_name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();
                $buyer_id = $this->buyerApi->createBuyer($external_identifier, $display_name);
                // assign buyer_id to quote object
                $quote->setData('gr4vy_buyer_id', $buyer_id);

                // save buyer to gr4vy_buyers table if external_identifier & display_name defined
                if ($external_identifier && $display_name && $buyer_id) {
                    $this->buyerData
                         ->setExternalIdentifier($external_identifier)
                         ->setDisplayName($display_name)
                         ->setBuyerId($buyer_id);

                    try {
                        $this->buyerRepository->save($this->buyerData);
                    }
                    catch (\Exception $e) {
                        $this->gr4vy_logger->logException($e);
                    }
                }
            }
        }

        $result = $proceed($quote);
    }
}

