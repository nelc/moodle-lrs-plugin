<?php

namespace local_moodle_lrs_plugin;

defined('MOODLE_INTERNAL') || die();

class hook_callbacks {
    public static function before_standard_head_html_generation(\core\hook\output\before_standard_head_html_generation $hook): void {
        global $PAGE;
        // Load iziToast CSS.
        $PAGE->requires->css('/local/moodle_lrs_plugin/assets/izitoast/css/iziToast.min.css', true);
    }

    public static function before_footer_html_generation(\core\hook\output\before_footer_html_generation $hook): void {
        global $PAGE;
        // Load iziToast JS.
        $PAGE->requires->js('/local/moodle_lrs_plugin/assets/izitoast/js/iziToast.min.js', true);
        // Load tracking JS.
        $PAGE->requires->js('/local/moodle_lrs_plugin/assets/js/frontend.js', true);
    }
}
