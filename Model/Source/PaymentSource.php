<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

use Gr4vy\Magento\Model\Payment\Gr4vy;

class PaymentSource implements \Magento\Framework\Option\ArrayInterface
{
    const SOURCE_ECOMMERCE = 'ecommerce';
    const SOURCE_CARD_ON_FILE = 'card_on_file';
    const SOURCE_INSTALLMENT = 'installment';
    const SOURCE_MOTO = 'moto';
    const SOURCE_RECURRING = 'recurring';

    public function toOptionArray()
    {
        return [
            ['value' => self::SOURCE_ECOMMERCE, 'label' => __('Ecommerce')],
            ['value' => self::SOURCE_CARD_ON_FILE, 'label' => __('Card On File')],
            ['value' => self::SOURCE_INSTALLMENT, 'label' => __('Installment')],
            ['value' => self::SOURCE_MOTO, 'label' => __('Moto')],
            ['value' => self::SOURCE_RECURRING, 'label' => __('Recurring')]
        ];
    }
}
