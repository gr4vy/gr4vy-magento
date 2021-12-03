<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    const ENV_DEV = 'sandbox';
    const ENV_PRD = 'production';

    public function toOptionArray()
    {
        return [
            ['value' => self::ENV_DEV, 'label' => __('Sandbox')],
            ['value' => self::ENV_PRD, 'label' => __('Production')],
        ];
    }

    public function toArray()
    {
        return [
            self::ENV_DEV => __('Sandbox'),
            self::ENV_PRD => __('Production')
        ];
    }
}
