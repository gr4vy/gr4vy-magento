<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Client;

use Gr4vy\Payment\Api\Data\BuyerInterface;
use Gr4vy\Payment\Api\Data\BuyerInterfaceFactory;
use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Magento\Framework\Api\DataObjectHelper;
use Gr4vy\Api\BuyersApi;
use Gr4vy\model\BuyerRequest;

class Buyer extends Base
{
    public function getApiIstance()
    {
        try {
            return new BuyersApi(new \GuzzleHttp\Client(), $this->getGr4vyConfig()->getConfig());
        }
        catch (\Exception $e) {
            $this->gr4vy_logger->logException($e);
        }
    }

    /**
     * create new Gr4vy buyer with display_name and external_identifier and return unique buyer_id
     *
     * @param string
     * @param string
     * @return string
     */
    public function createBuyer($external_identifier, $display_name)
    {
        $buyer_request = array('external_identifier' => $external_identifier, 'display_name' => $display_name);
        try {
            $this->gr4vy_logger->logMixed($buyer_request);
            $buyer = $this->getApiIstance()->addBuyer($buyer_request);

            $this->gr4vy_logger->logMixed($buyer->getId());
            return $buyer->getId();
        }
        catch (\Exception $e) {
            $this->gr4vy_logger->logException($e);
        }
    }

    /**
     * list buyers for current Gr4vy_Id
     *
     * @return array
     */
    public function listBuyers()
    {
        try {
            return $this->getApiIstance()->listBuyers();
        }
        catch (\Exception $e) {
            $this->gr4vy_logger->logException($e);
        }
    }
}
