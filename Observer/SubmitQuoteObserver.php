<?php

namespace Gr4vy\Magento\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class SubmitQuoteObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        // Keep cart active until such actions are taken
        $quote->setIsActive(true);
    }
}
