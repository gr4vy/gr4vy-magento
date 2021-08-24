<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Client;

use Gr4vy\api\TransactionsApi;

class Transaction extends Base
{

    /**
     * get transaction api instance
     */
    public function getApiInstance()
    {
        try {
            return new TransactionsApi(new \GuzzleHttp\Client(), $this->getGr4vyConfig()->getConfig());
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * retrieve details of transaction
     *
     * @param string
     * @return \Gr4vy\model\Transaction|\Gr4vy\model\Error401Unauthorized|\Gr4vy\model\ErrorGeneric
     */
    public function getTransactionDetail($transaction_id)
    {
        try {
            return $this->getApiInstance()->getTransaction($transaction_id);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * authorize new transaction
     *
     * @param  \Gr4vy\model\TransactionRequest $transaction_request transaction_request (optional)
     * @return boolean
     */
    public function authorize()
    {
    }

    /**
     * capture transaction online
     *
     * @param  string $transaction_id The ID for the transaction to get the information for. (required)
     * @param  \Gr4vy\model\TransactionCaptureRequest $transaction_capture_request transaction_capture_request (optional) - for partial capture
     *
     * @throws \Gr4vy\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Gr4vy\model\Transaction|\Gr4vy\model\ErrorGeneric|\Gr4vy\model\Error401Unauthorized|\Gr4vy\model\ErrorGeneric
     */
    public function capture($transaction_id, $transaction_capture_request = null)
    {
        try {
            return $this->getApiInstance()->captureTransaction($transaction_id, $transaction_capture_request);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }

    /**
     * refund transaction online
     */
    public function refund()
    {
        try {
            return $this->getApiInstance()->refundTransaction($transaction_id, $transaction_capture_request);
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }
}
