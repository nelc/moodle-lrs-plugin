<?php
namespace local_moodle_lrs_plugin;

defined('MOODLE_INTERNAL') || die();

class webservice {
    public static function video_watched($videoid, $courseid, $duration) {
        global $USER;

        // تحقق من صحة البيانات
        if (!$videoid || !$courseid || !$duration || !$duration ) {
            throw new \invalid_parameter_exception('Invalid parameters');
        }

        // الحصول على الـ context الخاص بالكورس
        $context = \context_course::instance($courseid);

        // تشغيل الحدث
        $event = \local_moodle_lrs_plugin\event\video_watched::create([
            'contextid' => $context->id,
            'objectid'  => $videoid,
            'userid'    => $USER->id,
            'other'     => [
                'courseid'  => $courseid,
                'duration'  => $duration,
            ]
        ]);
        $event->trigger();

        return ['status' => 'success', 'message' => 'Video watched event triggered.'];
    }
}
