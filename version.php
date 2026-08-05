<?php

/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/version.php
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_moodle_lrs_plugin';
$plugin->version   = 2026080500; // YYYYMMDDXX (تاريخ الإصدار)
$plugin->requires  = 2024042200; // الحد الأدنى من إصدار Moodle (Moodle 4.4)
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '2.0.6';
$plugin->dependencies = [
    'tool_courserating' => ANY_VERSION, // هذا البلاجن مطلوب
];