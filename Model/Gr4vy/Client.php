<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Gr4vy;

use Gr4vy\Payment\Api\Data\BuyerInterface;
use Gr4vy\Payment\Api\Data\BuyerInterfaceFactory;
use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Magento\Framework\Api\DataObjectHelper;
use Gr4vy\api\BuyersApi;
use Gr4vy\model\BuyerRequest;

class Client extends Base
{
    ///**
    // * @param array $data
    // */
    //public function __construct(
    //    \Magento\Framework\Model\Context $context,
    //    \Magento\Framework\Registry $registry,
    //    BuyerInterfaceFactory $buyerDataFactory,
    //    DataObjectHelper $dataObjectHelper,
    //    \Gr4vy\Payment\Model\ResourceModel\Buyer $resource,
    //    \Gr4vy\Payment\Model\ResourceModel\Buyer\Collection $resourceCollection,
    //    BuyersApi $sdkBuyerApi,
    //    array $data = []
    //) {
    //    $this->buyerDataFactory = $buyerDataFactory;
    //    $this->dataObjectHelper = $dataObjectHelper;
    //    $this->sdkBuyerApi = $sdkBuyerApi;
    //    parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    //}

    /**
     * create new Gr4vy buyer with display_name and external_identifier 
     *
     * @param string
     * @param string
     * @return Buyer
     */
    public function createBuyer($external_identifier, $display_name)
    {
        // debug start
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gr4vy.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($this->source_privatekey->getPrivateKeyDirAbsolutePath().DIRECTORY_SEPARATOR.$this->gr4vy_helper->getPrivateKey());
        //$buyer = $this->sdkBuyerApi->addBuyer();

        // save buyer to gr4vy_buyers table
        return 1;
    }
}
