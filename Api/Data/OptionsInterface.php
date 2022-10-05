<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Api\Data;

interface OptionsInterface
{
    const TMPL = 'payment/gr4vy_section/%1$s/%2$s';

    /**
     * available config options reference for additional gr4vy config values
     */
    const SECTION_API = 'api';
    const API = [
        'enabled' => 'active',
        'instructions' => 'instructions',
        'id' => 'id',
        'private_key' => 'private_key',
        'payment_action' => 'payment_action',
        'order_status' => 'order_status',
        'environment' => 'environment',
        'debug' => 'debug',
        'intent' => 'payment_action',
        'order_status' => 'order_status',
        'env' => 'environment',
        'store' => 'payment_store',
    ];

    const SECTION_OPTIONS = 'options';
    const OPTIONS = [
        'payment_source' => 'payment_source',
        'payment_store' => 'payment_store',
        'custom_data' => 'custom_data',
        'require_security_code' => 'require_security_code',
        'statement_descriptor' => [
            'name' => 'name',
            'description' => 'description',
            'city' => 'city',
            'phone_number' => 'phone_number',
            'url' => 'url',
        ]
    ];

    const SECTION_THEME = 'theme';
    const THEME = [
        'fonts' => [
            'body' => 'body'
        ],
        'colors' => [
            'text' => 'text',
            'subtle_text' => 'subtleText',
            'label_text' => 'labelText',
            'primary' => 'primary',
            'page_background' => 'pageBackground',
            'container_background_unchecked' => 'containerBackgroundUnchecked',
            'container_background' => 'containerBackground',
            'container_border' => 'containerBorder',
            'input_border' => 'inputBorder',
            'input_background' => 'inputBackground',
            'input_text' => 'inputText',
            'input_radio_border' => 'inputRadioBorder',
            'input_radio_border_checked' => 'inputRadioBorderChecked',
            'danger' => 'danger',
            'danger_background' => 'dangerBackground',
            'danger_text' => 'danger_text',
            'info' => 'info',
            'info_background' => 'infoBackground',
            'info_text' => 'infoText',
            'focus' => 'focus'
        ],
        'border_widths' => [
            'container' => 'container',
            'input' => 'input'
        ],
        'radii' => [
            'container' => 'container',
            'input' => 'input'
        ],
        'shadows' => [
            'focus_ring' => 'focusRing'
        ]
    ];
}

