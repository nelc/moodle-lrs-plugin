<?php
/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    [
        'eventname'   => '\core\event\user_enrolment_created',
        'callback'    => '\local_moodle_lrs_plugin\observer::user_enrolment_created',
    ],
    [
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => '\local_moodle_lrs_plugin\observer::course_activity_completion_updated',
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => '\local_moodle_lrs_plugin\observer::quiz_attempt_submitted',
    ],
    [
        'eventname'   => '\core\event\course_completed',
        'callback'    => '\local_moodle_lrs_plugin\observer::course_completed',
    ],
    [
        'eventname'   => '\mod_feedback\event\response_submitted',
        'callback'    => '\local_moodle_lrs_plugin\observer::course_reviewed',
    ],
    [
        'eventname' => '\tool_courserating\event\rating_created',
        'callback' => '\local_moodle_lrs_plugin\observer::rating_created',
    ],
    [
        'eventname'   => '\tool_courserating\event\rating_updated',
        'callback'    => '\local_moodle_lrs_plugin\observer::rating_updated',
    ],
    [
        'eventname' => '\local_moodle_lrs_plugin\event\video_watched',
        'callback'  => '\local_moodle_lrs_plugin\observer::video_watched',
        'includefile' => '/local/moodle_lrs_plugin/classes/observer.php',
        'priority'  => 1000,
    ],
);
