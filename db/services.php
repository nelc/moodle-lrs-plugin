 <?php
$functions = [
    'local_moodle_lrs_plugin_trigger_video_watched' => [
        'classname'   => 'local_moodle_lrs_plugin_external',
        'methodname'  => 'trigger_video_watched',
        'classpath'   => 'local/moodle_lrs_plugin/classes/external.php',
        'description' => 'Triggers a video watched event.',
        'type'        => 'write',
        'ajax'        => true,
    ],
];

$services = [
    'Moodle LRS Plugin Service' => [
        'functions' => ['local_moodle_lrs_plugin_trigger_video_watched'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'moodle_lrs_plugin_service',
    ],
];
