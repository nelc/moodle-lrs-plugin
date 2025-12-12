<?php
namespace local_moodle_lrs_plugin\event;

defined('MOODLE_INTERNAL') || die();

class video_watched extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // 'r' للقراءة فقط
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'video_logs';
    }

    public static function get_name() {
        return get_string('eventvideowatched', 'local_moodle_lrs_plugin');
    }

    public function get_description() {
        return "User with ID '{$this->userid}' watched video ID '{$this->objectid}' in course ID '{$this->other['courseid']}' for duration of '{$this->other['duration']}' seconds.";
    }

    public function get_url() {
        return new \moodle_url('/course/view.php', ['id' => $this->other['courseid']]);
    }
}
