<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Source;

use Gr4vy\Payment\Model\Payment\Gr4vy;

class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => Gr4vy::PAYMENT_TYPE_AUTH, 'label' => __('Authorize Only')],
            ['value' => Gr4vy::PAYMENT_TYPE_AUCAP, 'label' => __('Authorize & Capture')]
        ];
    }
}
