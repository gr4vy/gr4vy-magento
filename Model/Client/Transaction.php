<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

// use Gr4vy\api\TransactionsApi;
// use Gr4vy\model\TransactionCaptureRequest;
// use Gr4vy\model\TransactionRefundRequest;

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
     * @throws \Gr4vy\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Gr4vy\model\Transaction|\Gr4vy\model\ErrorGeneric|\Gr4vy\model\Error401Unauthorized|\Gr4vy\model\ErrorGeneric
     */
    public function refund($transaction_id, $amount = null)
    {
        try {
            $refund_request = array("amount"=>$amount);
            return $this->getApiInstance()->refundTransaction($transaction_id, $refund_request);
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
