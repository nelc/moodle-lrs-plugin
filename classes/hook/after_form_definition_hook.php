<?php

namespace local_moodle_lrs_plugin\hook;

use core_course\hook\after_form_definition;

class after_form_definition_hook extends after_form_definition {

    public static function execute(after_form_definition $hook): void {
        $mform = $hook->mform; // جلب نموذج المقرر الدراسي
        global $DB, $COURSE;

        // 🟢 إضافة الحقول داخل قسم "General"
        $mform->addElement('header', 'moodle_lrs_bzzix_fields_section', get_string('bzzix_fields_section', 'local_moodle_lrs_plugin'));

        // 🟢 مدة الدورة (ساعات ودقائق)
        if (!$mform->elementExists('course_duration')) {
            $duration_group = [];
            $duration_group[] = $mform->createElement('text', 'course_duration_hours', get_string('course_duration_hours', 'local_moodle_lrs_plugin'), ['size' => 2]);
            $duration_group[] = $mform->createElement('static', '', '', ' : ');
            $duration_group[] = $mform->createElement('text', 'course_duration_minutes', get_string('course_duration_minutes', 'local_moodle_lrs_plugin'), ['size' => 2]);

            $mform->addGroup($duration_group, 'course_duration', get_string('course_duration', 'local_moodle_lrs_plugin'), '', false);
            $mform->setType('course_duration_hours', PARAM_INT);
            $mform->setType('course_duration_minutes', PARAM_INT);
        }

        // 🟢 لغة الدورة
        if (!$mform->elementExists('course_language')) {
            $languages = [
                'ar-SA' => get_string('language_ar', 'local_moodle_lrs_plugin'),
                'en-US' => get_string('language_en', 'local_moodle_lrs_plugin'),
                'fr-FR' => get_string('language_fr', 'local_moodle_lrs_plugin'),
                'es-ES' => get_string('language_es', 'local_moodle_lrs_plugin'),
                'de-DE' => get_string('language_de', 'local_moodle_lrs_plugin'),
                'it-IT' => get_string('language_it', 'local_moodle_lrs_plugin'),
                'ru-RU' => get_string('language_ru', 'local_moodle_lrs_plugin'),
                'zh-CN' => get_string('language_zh', 'local_moodle_lrs_plugin'),
                'ja-JP' => get_string('language_ja', 'local_moodle_lrs_plugin'),
                'tr-TR' => get_string('language_tr', 'local_moodle_lrs_plugin'),
                'hi-IN' => get_string('language_hi', 'local_moodle_lrs_plugin'),
                'pt-BR' => get_string('language_pt', 'local_moodle_lrs_plugin')
            ];            
            $mform->addElement('select', 'course_language', get_string('course_language', 'local_moodle_lrs_plugin'), $languages);
            //$mform->setType('course_language', PARAM_ALPHANUM);
            $mform->setDefault('course_language', 'en-US');
        }

        // 🟢 تمكين الربط مع NELC
        if (!$mform->elementExists('is_nelc_enabled')) {
            $mform->addElement('advcheckbox', 'is_nelc_enabled', get_string('is_nelc_enabled', 'local_moodle_lrs_plugin'));
            $mform->setType('is_nelc_enabled', PARAM_INT);
            $mform->setDefault('is_nelc_enabled', 1);
        }

        // ✅ استرجاع القيم من قاعدة البيانات
        if (!empty($COURSE->id)) {
            $default_values = $DB->get_record('course', ['id' => $COURSE->id], 'course_duration, course_language, is_nelc_enabled');
            if ($default_values) {
                $mform->setDefault('course_duration_hours', floor($default_values->course_duration / 60));
                $mform->setDefault('course_duration_minutes', $default_values->course_duration % 60);
                $mform->setDefault('course_language', $default_values->course_language);
                $mform->setDefault('is_nelc_enabled', $default_values->is_nelc_enabled);
            }
        }

        if (get_class($mform) === 'mod_lesson_mod_form') {

            // إدراج الحقل بعد حقل الوصف مباشرة
            $mform->addElement('header', 'lessonsettings', get_string('lessonsettings', 'local_moodle_lrs_plugin'));
            $mform->addElement('text', 'lesson_duration', get_string('lessonduration', 'local_moodle_lrs_plugin'));
            $mform->setType('lesson_duration', PARAM_INT);
            $mform->addRule('lesson_duration', get_string('required'), 'required', null, 'client');
            $mform->addHelpButton('lesson_duration', 'lessonduration', 'local_moodle_lrs_plugin');

            // تعيين القيمة الحالية إذا كانت موجودة
            if (isset($hook->data->lesson_duration)) {
                $mform->setDefault('lesson_duration', $hook->data->lesson_duration);
            }
        }


    }
}
