<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

use Gr4vy\api\BuyersApi;
use Gr4vy\model\BuyerRequest;
use Gr4vy\model\BuyerUpdate;
use Gr4vy\model\BillingDetailsUpdateRequest;
use Gr4vy\model\Address as AddressUpdate;
use Gr4vy\model\Tax;

class Buyer extends Base
{
    const ERROR_CODE_GENERIC = '400';
    const ERROR_CODE_UNAUTHORIZED = '409';
    const ERROR_CODE_DUPLICATE = '409';

    const ERROR_DUPLICATE = 'duplicate';

    public function getApiInstance()
    {
        try {
            $api_instance =  new BuyersApi(new \GuzzleHttp\Client(), $this->getGr4vyConfig()->getConfig());
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }

        return $api_instance;
    }

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
        $buyer_update = new BuyerUpdate();
        $billing_details = new BillingDetailsUpdateRequest();
        try {
            $billing_details->setFirstName($billing_address['first_name']);
            $billing_details->setLastName($billing_address['last_name']);
            $billing_details->setEmailAddress($billing_address['email_address']);
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
                    $billing_details->setPhoneNumber($phoneNumber);  
                }
            }

        }
        catch (\InvalidArgumentException $e) {
            $this->gr4vyLogger->logException($e);
        }

        $address = new AddressUpdate();
        try {
            if (array_key_exists("address", $billing_address)) {
                $address->setCity($billing_address['address']['city']);
                $address->setCountry($billing_address['address']['country']);
                $address->setPostalCode($billing_address['address']['postal_code']);
                if ($billing_address['address']['state']) {
                    $address->setState($billing_address['address']['state']);
                }
                else {
                    // set state to country to fix gr4vy server error - suggested by Gr4vy
                    $address->setState($billing_address['address']['country']);
                }
                $address->setLine1($billing_address['address']['street']);
                $address->setLine2($billing_address['address']['street2']);
            }
        }
        catch (\InvalidArgumentException $e) {
            $this->gr4vyLogger->logException($e);
        }

        try {
            $billing_details->setAddress($address);
            $buyer_update->setBillingDetails($billing_details);
            $buyer = $this->getApiInstance()->updateBuyer($buyer_id, $buyer_update);

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
        $display_name = strval($display_name);
        $external_identifier = strval($external_identifier);
        $buyer_request = new BuyerRequest();
        if (strlen($display_name) > 0) {
            $buyer_request->setDisplayName($display_name);
        }
        if (strlen($external_identifier) > 0) {
            $buyer_request->setExternalIdentifier($external_identifier);
        }

        try {
            $buyer = $this->getApiInstance()->addBuyer($buyer_request);

            return $buyer->getId();
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
    public function listBuyers($id = null)
    {
        try {
            return $this->getApiInstance()->listBuyers($id);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
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

        if (is_array($buyers) && count($buyers) > 0) {
            list($buyer) = $buyers;
            return $buyer;
        }

        return false;
    }
}
