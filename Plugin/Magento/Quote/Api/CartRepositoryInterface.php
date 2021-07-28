<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Plugin\Magento\Quote\Api;

use Gr4vy\Payment\Api\BuyerRepositoryInterface;
use Gr4vy\Payment\Model\Gr4vy\Client as Gr4vyClient;

class CartRepositoryInterface
{
    /**
     * @var BuyerRepositoryInterface
     */
    protected $buyerRepository;

    /**
     * @var Gr4vyClient
     */
    protected $client;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        BuyerRepositoryInterface $buyerRepository,
        Gr4vyClient $client
    ) {
        $this->buyerRepository = $buyerRepository;
        $this->client = $client;
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
        //var_dump($quote->getData()); die;
        if ($gr4vy_buyer_id = $this->buyerRepository->get($quote->getCustomerId())->getGr4vyBuyerId()) {
            // check for customer in gr4vy_buyers table
            $quote->setGr4vyBuyerId($gr4vy_buyer_id);
        }
        else {
            // if customer not logged in, create anonymous buyer without external_identifier and display_name
            $external_identifier = $quote->getCustomerId();
            $display_name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();
            $buyer = $this->client->createBuyer($external_identifier, $display_name);
            $quote->setGr4vyBuyerId($buyer->getId());
        }

        $result = $proceed($quote);
    }
}

