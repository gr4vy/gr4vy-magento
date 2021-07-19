<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Payment;

use Gr4vy\Payment\Helper\Data as Gr4vyHelper;

class Gr4vy extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_TYPE_AUTH = 'AUTHONLY';
    const PAYMENT_TYPE_AUCAP = 'AUTHNCAPTURE';

    protected $_code = "gr4vy";

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

    /**
     * retrieve params to embed gr4vy checkout webform
     *
     * @return array
     */
    public function gr4vyWebCheckoutParams()
    {
    }

    /**
     * authorise payment request
     *
     * @return boolean
     */
    public function authorise()
    {
    }

    /**
     * Void payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        // method to void payment

        return $this;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        //$transaction = $this->transactionFactory->create()->load($order->getIncrementId());
        // send capture request and retrieve response
        //$response = ..

        //$payment->setTransactionId($response['transactionId']);
        //if ($response['status'] == self::PAYMENT_STATUS_SUCCESS) {
        //    return $this;
        //} else {
        //    if (!empty($outcome) && !empty($response['reasonMessage'])) {
        //        throw new \Exception($response['reasonMessage']);
        //    } else {
        //        throw new \Exception(__("Gr4vy capturing error."));
        //    }
        //}
    }

    /**
     * Refund capture
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();

        // send refund request and retrieve response
        //$response = ..

        //$payment->setTransactionId($response['transactionId']);
        //if ($response['type'] == self::PAYMENT_TYPE_REFUND && $response['status'] == self::PAYMENT_STATUS_SUCCESS) {
        //    return $this;
        //} else {
        //    if (!empty($outcome) && !empty($response['reasonMessage'])) {
        //        throw new \Exception($response['reasonMessage']);
        //    } else {
        //        throw new \Exception(__("Gr4vy refunding error."));
        //    }
        //}
    }


    /**
     * Cancel payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->void($payment);

        return $this;
    }
}

