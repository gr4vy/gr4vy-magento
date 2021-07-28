<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Plugin\Magento\Quote\Api;

class PaymentMethodManagementInterface
{
    /**
     * prepare gr4vy checkout embedded form 
     *
     * @return string redirect url or error message.
     */
    public function aroundSet(
        \Magento\Quote\Api\PaymentMethodManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $method
    ) {
        //var_dump($cartId);
        //var_dump($method->getData());
        $result = $proceed($cartId, $method);
        //var_dump($result); die;

        return $result;
    }
}

