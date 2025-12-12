<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

class local_moodle_lrs_plugin_external extends external_api {
    public static function trigger_video_watched_parameters() {
        return new external_function_parameters([
            'videoid' => new external_value(PARAM_INT, 'ID of the video'),
            'courseid' => new external_value(PARAM_INT, 'ID of the course'),
            'duration' => new external_value(PARAM_TEXT, 'Video duration in seconds'),
        ]);
    }

    public static function trigger_video_watched($videoid, $courseid, $duration) {
        global $DB, $USER;

        // تحقق من صحة البيانات
        $params = self::validate_parameters(self::trigger_video_watched_parameters(), [
            'videoid' => $videoid,
            'courseid' => $courseid,
            'duration' => $duration,
        ]);

        // الحصول على `context`
        $context = context_course::instance($params['courseid']);

        // تشغيل الحدث
        $event = \local_moodle_lrs_plugin\event\video_watched::create([
            'context'  => $context,
            'objectid' => $params['videoid'],
            'relateduserid' => $USER->id,
            'other'    => [
                'courseid' => $params['courseid'],
                'duration' => $params['duration']
            ],
        ]);

        $event->trigger();

        return ['status' => 'success', 'message' => 'Video watched event triggered.'];
    }

    public static function trigger_video_watched_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status'),
            'message' => new external_value(PARAM_TEXT, 'Message'),
        ]);
    }
}
