<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    const ENV_DEV = 'development';
    const ENV_STG = 'staging';
    const ENV_PRD = 'production';

    public function toOptionArray()
    {
        return [
            ['value' => self::ENV_DEV, 'label' => __('Development')],
            ['value' => self::ENV_STG, 'label' => __('Staging')],
            ['value' => self::ENV_PRD, 'label' => __('Production')],
        ];
    }

    public function toArray()
    {
        return [
            self::ENV_DEV => __('Development'),
            self::ENV_STG => __('Staging'),
            self::ENV_PRD => __('Production')
        ];
    }
}
