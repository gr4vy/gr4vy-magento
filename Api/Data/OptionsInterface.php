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
    const API_ENABLED = 'active';
    const API_INSTRUCTION = 'instructions';
    const API_PRIVATE_KEY = 'private_key';
    const API_DEBUG = 'debug';
    const API_ID = 'id';
    const API_INTENT = 'payment_action';
    const API_ORDER_STATUS = 'order_status';
    const API_ENV = 'environment';
    const API_STORE = 'payment_store';
    const API_CUSTOM_DATA = 'custom_data';

    const SECTION_OPTIONS = 'options';
    const OPTIONS_PAYMENT_SOURCE = 'payment_source';
    const OPTIONS_CUSTOM_DATA = 'custom_data';
    const OPTIONS_STATEMENT_DESCRIPTOR = 'statement_descriptor';
    const OPTIONS_SD_NAME = 'sd_name';
    const OPTIONS_SD_DESCRIPTION = 'sd_description';
    const OPTIONS_SD_CITY = 'sd_city';
    const OPTIONS_SD_PHONE = 'sd_phonenumber';
    const OPTIONS_SD_URL = 'sd_url';

    const SECTION_THEME = 'theme';
    const THEME_FONTS = 'fonts';
    const THEME_FONT_BODY = 'font_body';
    const THEME_COLORS = 'colors';
    const THEME_COLOR_TEXT = 'color_text';
    const THEME_COLOR_SUBTLE_TEXT = 'color_subtle_text';
    const THEME_COLOR_LABLE_TEXT = 'color_label_text';
    const THEME_COLOR_PRIMARY = 'color_primary';
    const THEME_COLOR_PAGE_BACKGROUND = 'color_page_background';
    const THEME_COLOR_CONTAINER_BACKGROUND_UNCHECKED = 'color_container_background_unchecked';
    const THEME_COLOR_CONTAINER_BACKGROUND = 'color_container_background';
    const THEME_COLOR_CONTAINER_BORDER = 'color_container_border';
    const THEME_COLOR_INPUT_BORDER = 'color_input_border';
    const THEME_COLOR_INPUT_BACKGROUND = 'color_input_background';
    const THEME_COLOR_INPUT_TEXT = 'color_input_text';
    const THEME_COLOR_INPUT_RADIO_BORDER = 'color_input_radio_border';
    const THEME_COLOR_INPUT_RADIO_BORDER_CHECKED = 'color_input_radio_border_checked';
    const THEME_COLOR_DANGER = 'color_danger';
    const THEME_COLOR_DANGER_BACKGROUND = 'color_danger_background';
    const THEME_COLOR_DANGER_TEXT = 'color_danger_text';
    const THEME_COLOR_INFO = 'color_info';
    const THEME_COLOR_INFO_BACKGROUND = 'color_info_background';
    const THEME_COLOR_INFO_TEXT = 'color_info_text';
    const THEME_COLOR_FOCUS = 'color_focus';
    const THEME_BORDERS = 'borders';
    const THEME_BORDER_CONTAINER = 'border_container';
    const THEME_BORDER_INPUT = 'border_input';
    const THEME_RADII = 'radii';
    const THEME_RADII_CONTAINER = 'radii_container';
    const THEME_RADII_INPUT = 'radii_input';
    const THEME_FOCUS_RING = 'focus_ring';
    const THEME_FOCUS_RING_SHADOWS = 'focus_ring_shadows';
}

