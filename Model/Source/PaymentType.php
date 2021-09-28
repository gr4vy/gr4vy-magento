<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

class PaymentType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_WEB = 'webcheckout';

    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_WEB, 'label' => __('Gr4vy Web Checkout')]
        ];
    }

    public function toArray()
    {
        return [
            self::TYPE_WEB => __('Gr4vy Web Checkout')
        ];
    }
}
