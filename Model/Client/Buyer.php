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
    const ERROR_CODE_GENERIC = '400';
    const ERROR_CODE_UNAUTHORIZED = '409';
    const ERROR_CODE_DUPLICATE = '409';

    const ERROR_DUPLICATE = 'duplicate';

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
        $display_name = strval($display_name);
        $external_identifier = strval($external_identifier);
        $buyer_request = array('display_name' => $display_name, 'external_identifier' => $external_identifier);
        try {
            //$this->gr4vy_logger->logMixed($buyer_request);
            $buyer = $this->getApiIstance()->addBuyer($buyer_request);

            return $buyer->getId();
        }
        catch (\Exception $e) {
            $this->gr4vy_logger->logException($e);
            if ($e->getCode() == self::ERROR_CODE_DUPLICATE) {
                return self::ERROR_DUPLICATE;
            }
        }
    }

    /**
     * list gr4vy buyers
     *
     * @param mixed string|null
     * @return array
     */
    public function listBuyers($id = null)
    {
        try {
            return $this->getApiIstance()->listBuyers($id);
        }
        catch (\Exception $e) {
            $this->gr4vy_logger->logException($e);
        }
    }

    /**
     * get buyer by Gr4vy_Id or external_identifier
     *
     * @param string - can be either gr4vy_buyer_id or external_identifier
     * @return array
     */
    public function getBuyer($id)
    {
        $id = strval($id);
        $buyers = $this->listBuyers($id)->getItems();
        return $buyers[0];
    }
}
