<?php

/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */


defined('MOODLE_INTERNAL') || die();

function local_moodle_lrs_plugin_extend_navigation($nav) {
    global $PAGE;
    
    // إضافة CSS الخاص بـ Izitoast
    $PAGE->requires->css('/local/moodle_lrs_plugin/assets/izitoast/css/iziToast.min.css', true);

    // إضافة JavaScript الخاص بـ Izitoast
    $PAGE->requires->js('/local/moodle_lrs_plugin/assets/izitoast/js/iziToast.min.js', true);
        
    $PAGE->requires->js('/local/moodle_lrs_plugin/assets/js/frontend.js', true);
}

// التحقق من نوع الوحدة الدراسية من رابط التحرير
function get_module_type_from_url() {
    global $PAGE;
    $url = $PAGE->url->get_path();

    if (strpos($url, '/course/modedit.php') === false) {
        return false;
    }

    // التحقق مما إذا كنا نضيف أو نحدّث وحدة دراسية
    $moduletype = optional_param('add', '', PARAM_ALPHANUM);
    $updateid = optional_param('update', 0, PARAM_INT);

    if ($updateid > 0) {
        $cm = get_coursemodule_from_id('', $updateid);
        if ($cm) {
            if ($cm->modname == 'lesson') return 'lesson';
            if ($cm->modname == 'resource') return 'resource';
        }
        return false;
    }

    if ($moduletype == 'lesson') return 'lesson';
    if ($moduletype == 'resource') return 'resource';

    return false;
}

//إضافة مدة الدرس أو المورد في صفحة التحرير
function local_moodle_lrs_plugin_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;

    $module_type = get_module_type_from_url();
    if (!$module_type) {
        return;
    }

    // إضافة الحقل فقط إذا لم يكن مضافًا مسبقًا
    if (!$mform->elementExists('lesson_duration')) {

        $mform->addElement('header', 'lesson_duration_header', get_string('bzzix_fields_section', 'local_moodle_lrs_plugin'));

        $lesson_duration_group = [];
        $lesson_duration_group[] = $mform->createElement('text', 'lesson_duration_hours', get_string('hours', 'local_moodle_lrs_plugin'), ['size' => 2]);
        $lesson_duration_group[] = $mform->createElement('static', '', '', ' : ');
        $lesson_duration_group[] = $mform->createElement('text', 'lesson_duration_minutes', get_string('minutes', 'local_moodle_lrs_plugin'), ['size' => 2]);

        $mform->addGroup($lesson_duration_group, 'lesson_duration', get_string('lesson_duration', 'local_moodle_lrs_plugin'), '', false);
        $mform->setType('lesson_duration_hours', PARAM_INT);
        $mform->setType('lesson_duration_minutes', PARAM_INT);
    }

    // تحميل القيم المخزنة عند التعديل
    $updateid = optional_param('update', 0, PARAM_INT);
    if ($updateid > 0) {
        $cm = get_coursemodule_from_id('', $updateid);
        if ($cm) {
            $table_name = ($module_type == 'lesson') ? 'lesson' : 'resource';
            $record = $DB->get_record($table_name, ['id' => $cm->instance], 'lesson_duration');

            if ($record && isset($record->lesson_duration)) {
                $total_minutes = (int)$record->lesson_duration;
                $hours = floor($total_minutes / 60);
                $minutes = $total_minutes % 60;
            
                $mform->setDefault('lesson_duration_hours', $hours);
                $mform->setDefault('lesson_duration_minutes', $minutes);
            }
        }
    }
}

// حفظ بيانات الدرس أو المورد
function local_moodle_lrs_plugin_coursemodule_edit_post_actions($data) {
    global $DB;

    if (in_array($data->modulename, ['lesson', 'resource']) && isset($data->lesson_duration_hours) && isset($data->lesson_duration_minutes)) {
        $hours = (int)$data->lesson_duration_hours;
        $minutes = (int)$data->lesson_duration_minutes;

        // تحويل الساعات والدقائق إلى إجمالي الدقائق
        $total_minutes = ($hours * 60) + $minutes;
        if ($total_minutes < 0) {
            $total_minutes = 0;
        }

        // تحديث حقل lesson_duration في الجدول المناسب
        $table_name = ($data->modulename == 'lesson') ? 'lesson' : 'resource';
        $record = $DB->get_record($table_name, ['id' => $data->instance]);

        if ($record) {
            $record->lesson_duration = $total_minutes;
            $record->timemodified = time();
            $DB->update_record($table_name, $record);
        }
    }

    return $data;
}

