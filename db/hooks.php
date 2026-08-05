<?php

$callbacks = [
    [
        'hook' => core_course\hook\after_form_definition::class,
        'callback' => ['\local_moodle_lrs_plugin\hook\after_form_definition_hook', 'execute'],
    ],
    [
        'hook' => core_course\hook\after_form_submission::class,
        'callback' => ['\local_moodle_lrs_plugin\hook\after_form_submission_hook', 'execute'],
    ],
    [
        'hook' => \core\hook\output\before_standard_head_html_generation::class,
        'callback' => ['\local_moodle_lrs_plugin\hook_callbacks', 'before_standard_head_html_generation'],
    ],
    [
        'hook' => \core\hook\output\before_footer_html_generation::class,
        'callback' => ['\local_moodle_lrs_plugin\hook_callbacks', 'before_footer_html_generation'],
    ],
];
