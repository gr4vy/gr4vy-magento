<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

// use Gr4vy\api\BuyersApi;
// use Gr4vy\model\BuyerRequest;
// use Gr4vy\model\BuyerUpdate;
// use Gr4vy\model\BillingDetailsUpdateRequest;
// use Gr4vy\model\Address as AddressUpdate;
// use Gr4vy\model\Tax;

class Buyer extends Base
{
    const ERROR_CODE_GENERIC = '400';
    const ERROR_CODE_UNAUTHORIZED = '409';
    const ERROR_CODE_DUPLICATE = '409';

    const ERROR_DUPLICATE = 'duplicate';

    /**
     * update existing Gr4vy buyer
     *
     * @param string
     * @param string
     * @param array
     * @return array
     */
    public function updateBuyer($buyer_id, $billing_address)
    {
        $buyer_update = array(
            "billing_details"=>array(
                "first_name"=>$billing_address['first_name'],
                "last_name"=>$billing_address['last_name'],
                "email_address"=>$billing_address['email_address']
            )
        );
        if (isset($billing_address) && isset($billing_address["phone_number"])) {
            $phoneNumber = preg_replace("/[^0-9]/", "", $billing_address['phone_number']);
            if ($phoneNumber) {
                $character = "0";
                if (strpos($phoneNumber, $character) === 0) {
                    $phoneNumber = substr($phoneNumber, 1);
                }
                $character = "+";
                if (strpos($phoneNumber, $character) !== 0) {
                    $phoneNumber = "+" . $phoneNumber;
                }
                $buyer_update["billing_details"]["phone_number"] =$phoneNumber; 
            }
        }

        if (array_key_exists("address", $billing_address)) {
            $buyer_update["address"] = array(
                "city"=>$billing_address['address']['city'],
                "country"=>$billing_address['address']['country'],
                "postal_code"=>$billing_address['address']['postal_code'],
                "line1"=>$billing_address['address']['street'],
                "line2"=>$billing_address['address']['street2']

            );
            if ($billing_address['address']['state']) {
                $buyer_update["address"]["state"] = $billing_address['address']['state'];
            }
            else {
                // set state to country to fix gr4vy server error - suggested by Gr4vy
                $buyer_update["address"]["state"] = $billing_address['address']['country'];
            }
        }

        try {
            $buyer = $this->getGr4vyConfig()->updateBuyer($buyer_id, $buyer_update);

            return $buyer;
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
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
        $buyer_request = array(
            "display_name"=>strval($display_name),
            "external_identifier"=>strval($external_identifier)
        );
        
        try {
            $buyer = $this->getGr4vyConfig()->addBuyer($buyer_request);

            return $buyer["id"];
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
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
    public function listBuyers($external_identifier = null)
    {
        try {
            $params = array(
                "search"=>$external_identifier,
            );
            return $this->getGr4vyConfig()->listBuyers($params);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * get buyer by Gr4vy_Id or external_identifier
     *
     * @param string - can be external_identifier
     * @return array
     */
    public function getBuyer($external_identifier)
    {
        $id = strval($external_identifier);
        $buyers = $this->listBuyers($id)["items"];
        if (is_array($buyers) && count($buyers) > 0) {
            list($buyer) = $buyers;
            return $buyer;
        }

        return false;
    }
}
