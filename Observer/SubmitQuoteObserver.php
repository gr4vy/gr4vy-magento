<?php

namespace Gr4vy\Magento\Observer;

use Gr4vy\Magento\Model\Payment\Gr4vy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class SubmitQuoteObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quotePayment = $quote->getPayment();

        if ($quotePayment->getMethod() != Gr4vy::PAYMENT_METHOD_CODE) {
            return;
        }

        // Keep cart active until such actions are taken
        $quote->setIsActive(true);
    }
}
