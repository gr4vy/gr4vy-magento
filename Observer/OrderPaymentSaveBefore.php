<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Observer;

use Gr4vy\Magento\Model\Payment\Gr4vy;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Magento\Quote\Model\QuoteFactory;

class OrderPaymentSaveBefore implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param Gr4vyLogger $gr4vyLogger
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        Gr4vyLogger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper,
        QuoteFactory $quoteFactory
    ) {
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $orderPayment = $observer->getEvent()->getPayment();

        if ($orderPayment->getMethod() != Gr4vy::PAYMENT_METHOD_CODE) {
            $this->gr4vyLogger->logMixed(['Processing Non Gr4vy Order']);
            return;
        }
    
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            $order = $orderPayment->getOrder();
            $quote = $this->quoteFactory->create()->load($order->getQuoteId());
            $quotePayment = $quote->getPayment();

            $this->gr4vyLogger->logMixed(['save to order payment' => $quotePayment->getData('gr4vy_transaction_id')]);
            if ($gr4vy_transaction_id = $quotePayment->getData('gr4vy_transaction_id')) { // remove hardcode later
                try {
                    $orderPayment->setData('gr4vy_transaction_id', $gr4vy_transaction_id);
                    $orderPayment->setData('transaction_id', $gr4vy_transaction_id);
                    $orderPayment->setData('last_trans_id', $gr4vy_transaction_id);
                }
                catch (\Exception $e) {
                    $this->gr4vyLogger->logException($e);
                }
            }
        }
    }
}

