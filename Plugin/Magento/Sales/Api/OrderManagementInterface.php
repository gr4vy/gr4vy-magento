<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Plugin\Magento\Sales\Api;

class OrderManagementInterface
{
    public function aroundPlace(
        \Magento\Sales\Api\OrderManagementInterface $subject,
        \Closure $proceed,
        $order
    ) {
        $result = $proceed($order);
        return $result;
    }
}

