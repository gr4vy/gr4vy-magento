<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Observer;

class CallbackUpdateOrder implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer , do 2 things
     * 1. create new buyer if doesn't exist - external id should be defined on the gr4vy_buyers table
     * 2. generate external id using magento customer id combine with gr4vy id
     * 3. generate new gr4vy token for quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        // create buyer
    }
}

