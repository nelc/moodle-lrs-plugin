<?php

function xmldb_local_moodle_lrs_plugin_install() {
    global $DB;

    $dbman = $DB->get_manager();

    // course table
    $table = new xmldb_table('course');

    $duration_field = new xmldb_field('course_duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $language_field = new xmldb_field('course_language', XMLDB_TYPE_CHAR, '10', null, null, null, 'en-US');
    $nelc_field = new xmldb_field('is_nelc_enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

    if (!$dbman->field_exists($table, $duration_field)) {
        $dbman->add_field($table, $duration_field);
    }
    if (!$dbman->field_exists($table, $language_field)) {
        $dbman->add_field($table, $language_field);
    }
    if (!$dbman->field_exists($table, $nelc_field)) {
        $dbman->add_field($table, $nelc_field);
    }

    // lesson table
    $lesson_table = new xmldb_table('lesson');
    $lesson_duration = new xmldb_field('lesson_duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    if (!$dbman->field_exists($lesson_table, $lesson_duration)) {
        $dbman->add_field($lesson_table, $lesson_duration);
    }

    // resource table
    $resource_table = new xmldb_table('resource');
    $resource_duration = new xmldb_field('lesson_duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    if (!$dbman->field_exists($resource_table, $resource_duration)) {
        $dbman->add_field($resource_table, $resource_duration);
    }


    // إنشاء قسم وحقل مخصص للهوية الوطنية إذا لم يكن موجوداً
    $categoryname = 'NELC Profile Fields';
    $customcategory = $DB->get_record('user_info_category', ['name' => $categoryname]);
    if (!$customcategory) {
        $customcategory = new \stdClass();
        $customcategory->name = $categoryname;
        $customcategory->sortorder = 99;
        $customcategory->id = $DB->insert_record('user_info_category', $customcategory);
    }

    if (!$DB->record_exists('user_info_field', ['shortname' => 'national_id'])) {
        $field = new \stdClass();
        $field->shortname = 'national_id';
        $field->name = 'رقم الهوية الوطنية / الإقامة';
        $field->datatype = 'text';
        $field->description = 'مطلوب للمركز الوطني للتعليم الإلكتروني (NELC)';
        $field->descriptionformat = 1;
        $field->categoryid = $customcategory->id;
        $field->sortorder = 1;
        $field->required = 1;
        $field->locked = 0;
        $field->visible = 2;
        $field->forceunique = 0;
        $field->signup = 1;
        $field->defaultdata = '';
        $field->defaultdataformat = 0;
        $field->param1 = 30;
        $field->param2 = 50;
        $DB->insert_record('user_info_field', $field);
    }

    return true;
}

