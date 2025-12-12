<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_moodle_lrs_plugin_category', get_string('pluginname', 'local_moodle_lrs_plugin')));

    // إنشاء التبويبات
    $tabs = array();
    $tabs[] = new tabobject('settings', new moodle_url('/admin/settings.php', array('section' => 'local_moodle_lrs_plugin_settings')), get_string('generalsettings', 'local_moodle_lrs_plugin'));
    $tabs[] = new tabobject('test', new moodle_url('/local/moodle_lrs_plugin/admin/test_connection.php'), get_string('test_connection', 'local_moodle_lrs_plugin'));

    // التبويب الأول: الإعدادات العامة
    $settings = new admin_settingpage('local_moodle_lrs_plugin_settings', get_string('generalsettings', 'local_moodle_lrs_plugin'));

    $settings->add(new admin_setting_heading('local_moodle_lrs_plugin_settings_header', '', $OUTPUT->tabtree($tabs, 'settings')));

    // إعدادات endpoint.
    $settings->add(new admin_setting_configtext(
        'local_moodle_lrs_plugin/endpoint',
        get_string('endpoint', 'local_moodle_lrs_plugin'),
        get_string('endpoint_desc', 'local_moodle_lrs_plugin'),
        '',
        PARAM_URL
    ));

    // إعدادات username.
    $settings->add(new admin_setting_configtext(
        'local_moodle_lrs_plugin/username',
        get_string('username', 'local_moodle_lrs_plugin'),
        get_string('username_desc', 'local_moodle_lrs_plugin'),
        '',
        PARAM_TEXT
    ));

    // إعدادات password.
    $settings->add(new admin_setting_configpasswordunmask(
        'local_moodle_lrs_plugin/password',
        get_string('password', 'local_moodle_lrs_plugin'),
        get_string('password_desc', 'local_moodle_lrs_plugin'),
        '',
        PARAM_TEXT
    ));

    // اسم المنصة.
    $settings->add(new admin_setting_configtext(
        'local_moodle_lrs_plugin/platformname',
        get_string('platformname', 'local_moodle_lrs_plugin'),
        get_string('platformname_desc', 'local_moodle_lrs_plugin'),
        '',
        PARAM_TEXT
    ));

    // اسم المنصة باللغة العربية.
    $settings->add(new admin_setting_configtext(
        'local_moodle_lrs_plugin/platformname_ar',
        get_string('platformname_ar', 'local_moodle_lrs_plugin'),
        get_string('platformname_ar_desc', 'local_moodle_lrs_plugin'),
        '',
        PARAM_TEXT
    ));

    // اسم المنصة باللغة الإنجليزية.
    $settings->add(new admin_setting_configtext(
        'local_moodle_lrs_plugin/platformname_en',
        get_string('platformname_en', 'local_moodle_lrs_plugin'),
        get_string('platformname_en_desc', 'local_moodle_lrs_plugin'),
        '',
        PARAM_TEXT
    ));

    $ADMIN->add('local_moodle_lrs_plugin_category', $settings);

    // التبويب الثاني: صفحة اختبار الاتصال
    $ADMIN->add('local_moodle_lrs_plugin_category', new admin_externalpage(
        'local_moodle_lrs_plugin_test',
        get_string('test_connection', 'local_moodle_lrs_plugin'),
        new moodle_url('/local/moodle_lrs_plugin/admin/test_connection.php')
    ));
    
}