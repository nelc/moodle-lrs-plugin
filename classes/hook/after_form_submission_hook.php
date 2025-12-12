<?php

namespace local_moodle_lrs_plugin\hook;

use core_course\hook\after_form_submission;
use stdClass;

class after_form_submission_hook extends after_form_submission {

    public static function execute(after_form_submission $hook): void {
        global $DB;

        $data = $hook->data;

        // التحقق من وجود معرف الدورة التدريبية
        if (empty($data->id)) {
            debugging('No course ID found in after_form_submission', DEBUG_DEVELOPER);
            return;
        }

        $courseid = (int) $data->id;
        $hours = !empty($data->course_duration_hours) ? (int) $data->course_duration_hours : 0;
        $minutes = !empty($data->course_duration_minutes) ? (int) $data->course_duration_minutes : 0;
        $duration = ($hours * 60) + $minutes;

        $course_language = !empty($data->course_language) ? $data->course_language : 'en-US';
        $is_nelc_enabled = isset($data->is_nelc_enabled) ? (int) $data->is_nelc_enabled : 1;

        // print_r($data);
        // exit;
        if ($courseid > 0) {
            $course = new stdClass();
            $course->id = $courseid;
            $course->course_duration = $duration;
            $course->course_language = $course_language;
            $course->is_nelc_enabled = $is_nelc_enabled;

            $DB->update_record('course', $course);
        }
    }
}
