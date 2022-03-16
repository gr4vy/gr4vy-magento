<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

use Gr4vy\Magento\Model\Payment\Gr4vy;

class PaymentStore implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => Gr4vy::PAYMENT_STORE_ASK, 'label' => __('Ask')],
            ['value' => Gr4vy::PAYMENT_STORE_YES, 'label' => __('Yes')],
            ['value' => Gr4vy::PAYMENT_STORE_NO, 'label' => __('No')]
        ];
    }
}
