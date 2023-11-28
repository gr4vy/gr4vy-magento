<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

class Transaction extends Base
{

    /**
     * retrieve details of transaction
     *
     * @param string
     * @return Array
     */
    public function getTransactionDetail($transaction_id)
    {
        try {
            return $this->getGr4vyConfig()->getTransaction($transaction_id);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * authorize new transaction
     *
     * @return boolean
     */
    public function authorize()
    {
        // skipped because of webform checkout
    }

    /**
     * capture transaction online
     *
     * @param  string $transaction_id The ID for the transaction to get the information for. (required)
     * @param  float (optional) - for partial capture
     *
     * @throws \InvalidArgumentException
     * @return Array
     */
    public function capture($transaction_id, $amount = null)
    {
        try {
            $transaction_capture_request = array("amount"=>$amount);
            return $this->getGr4vyConfig()->captureTransaction($transaction_id, $transaction_capture_request);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * refund transaction online
     *
     * @param  string $transaction_id The ID for the transaction to get the information for. (required)
     * @param  float (optional) - for partial capture
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function refund($transaction_id, $amount = null)
    {
        try {
            $refund_request = array("amount"=>$amount);
            return $this->getGr4vyConfig()->refundTransaction($transaction_id, $refund_request);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * void transaction
     *
     * @param string
     * @return Array
     */
    public function void($transaction_id)
    {
        try {
            return $this->getGr4vyConfig()->voidTransaction($transaction_id);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }


    /**
     * receive transaction status
     *
     * @param string
     * @return string
     */
    public function getStatus($transaction_id)
    {
        try {
            $transaction = $this->getTransactionDetail($transaction_id);
            return $transaction["status"];
        }
        catch (\Exception $e) {
            // default behavior
            $this->gr4vyLogger->logException($e);
        }
    }
}
