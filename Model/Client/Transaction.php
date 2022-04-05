<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

use Gr4vy\api\TransactionsApi;
use Gr4vy\model\TransactionCaptureRequest;
use Gr4vy\model\TransactionRefundRequest;

class Transaction extends Base
{

    /**
     * get transaction api instance
     */
    public function getApiInstance()
    {
        try {
            $config = $this->getGr4vyConfig()->getConfig();
            // $this->gr4vyLogger->logMixed(['auth' => $config->getAccessToken()->__toString()]);
            return new TransactionsApi(new \GuzzleHttp\Client(), $config);
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
        // skipped because of webform checkout
    }

    /**
     * capture transaction online
     *
     * @param  string $transaction_id The ID for the transaction to get the information for. (required)
     * @param  float (optional) - for partial capture
     *
     * @throws \Gr4vy\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Gr4vy\model\Transaction|\Gr4vy\model\ErrorGeneric|\Gr4vy\model\Error401Unauthorized|\Gr4vy\model\ErrorGeneric
     */
    public function capture($transaction_id, $amount = null)
    {
        try {
            $transaction_capture_request = new TransactionCaptureRequest();
            $transaction_capture_request->setAmount($amount);
            return $this->getApiInstance()->captureTransaction($transaction_id, $transaction_capture_request);
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
            $transaction_capture_request = new TransactionRefundRequest();
            $transaction_capture_request->setAmount($amount);
            return $this->getApiInstance()->refundTransaction($transaction_id, $transaction_capture_request);
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
            $model = $this->getApiInstance()->getTransaction($transaction_id);
            return $model->getStatus();
        }
        catch (\Gr4vy\ApiException $e) {
            echo($e->getMessage());
        }
        catch (\Exception $e) {
            // default behavior
            $this->gr4vyLogger->logException($e);
        }
    }
}
