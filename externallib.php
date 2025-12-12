<?php
namespace local_moodle_lrs_plugin;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

class external extends \external_api {
    public static function trigger_video_watched($videoid, $courseid, $duration = null) {
        global $USER;

        // تأكد من صلاحية المستخدم
        require_login();

        // تحقق من صحة المدخلات
        if (!$videoid || !$courseid) {
            throw new \invalid_parameter_exception('Invalid video or course ID.');
        }

        // الحصول على السياق
        $context = \context_course::instance($courseid);

        // إنشاء الحدث
        $event = \local_moodle_lrs_plugin\event\video_watched::create([
            'contextid' => $context->id,
            'objectid'  => $videoid,
            'userid'    => $USER->id,
            'other'     => [
                'courseid' => $courseid,
                'duration' => $duration ?? 0,
            ]
        ]);

        // تشغيل الحدث
        $event->trigger();

        return ['status' => 'success', 'message' => 'Event triggered successfully'];
    }

    public static function trigger_video_watched_parameters() {
        return new \external_function_parameters([
            'videoid'  => new \external_value(PARAM_INT, 'Video ID'),
            'courseid' => new \external_value(PARAM_INT, 'Course ID'),
            'duration' => new \external_value(PARAM_TEXT, 'Video duration in seconds', VALUE_OPTIONAL)
        ]);
    }

    public static function trigger_video_watched_returns() {
        return new \external_single_structure([
            'status'  => new \external_value(PARAM_TEXT, 'Status'),
            'message' => new \external_value(PARAM_TEXT, 'Message')
        ]);
    }
}
