<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_login();

$videoid = required_param('videoid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$duration = required_param('duration', PARAM_INT);

$context = context_course::instance($courseid);
require_capability('mod/page:view', $context);

$event = \local_moodle_lrs_plugin\event\video_watched::create([
    'context'  => $context, // ✅ استخدام `context` بدلاً من `contextid`
    'objectid' => $videoid,
    'relateduserid' => $USER->id, // ✅ استخدام `relateduserid` بدل `userid`
    'other'    => [
        'courseid' => $courseid,
        'duration' => $duration, // ✅ إصلاح الخطأ وتمرير القيمة الصحيحة
    ],
]);

$event->trigger();

// ✅ تعيين الهيدر المناسب لاستجابة JSON
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
exit; // ✅ استخدام `exit` بدل `die()` لتحسين الممارسة
