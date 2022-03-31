<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Quote\Api;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Customer as CustomerHelper;

class CartRepositoryInterface
{
    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @param Gr4vyHelper $gr4vyHelper
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        Gr4vyHelper $gr4vyHelper,
        CustomerHelper $customerHelper
    ) {
        $this->gr4vyHelper = $gr4vyHelper;
        $this->customerHelper = $customerHelper;
    }

    /**
     * whenever cart is saved, interact with gr4vy
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function aroundSave(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            $this->customerHelper->connectQuoteWithGr4vy($quote);
        }

        // NOTE: additional fix for multishipping checkout issue - somehow it affected gr4vy https://github.com/magento/magento2/pull/26637
        // issue in vendor/magento/module-quote/Model/QuoteAddressValidator.php [function] validateForCart
        // when customer logged in, $cart->getCustomerIsGuest() still return true
        if ($quote->getCustomer() && $quote->getCustomer()->getId()) {
            $quote->setCustomerIsGuest(false);
        }

        $result = $proceed($quote);

        return $result;
    }
}
