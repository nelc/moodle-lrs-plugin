<?php
/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
//require_once(__DIR__ . '/../Interactions/XapiIntegration.php');
use local_moodle_lrs_plugin\Interactions\XapiIntegration;


admin_externalpage_setup('local_moodle_lrs_plugin_test');

// إنشاء التبويبات
$PAGE->set_url(new moodle_url('/local/moodle_lrs_plugin/admin/test_connection.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

$settingsnode = $PAGE->settingsnav->find('local_moodle_lrs_plugin_category', navigation_node::TYPE_CONTAINER);
if ($settingsnode) {
    $settingsnode->make_active();
    $PAGE->navbar->add($settingsnode->text, $settingsnode->action);
}

$tabs = array();
$tabs[] = new tabobject('settings', new moodle_url('/admin/settings.php', array('section' => 'local_moodle_lrs_plugin_settings')), get_string('generalsettings', 'local_moodle_lrs_plugin'));
$tabs[] = new tabobject('test', new moodle_url('/local/moodle_lrs_plugin/admin/test_connection.php'), get_string('test_connection', 'local_moodle_lrs_plugin'));

// تعريف النموذج
class local_moodle_lrs_plugin_test_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $options = array(
            'registered' =>  'registered',
            'initialized' => 'initialized',
            'watched' => 'watched',
            'completed' => 'completed',
            'completedUnit' => 'completedUnit',
            'progressed' => 'progressed',
            'attempted' => 'attempted',
            'completedCourse' => 'completedCourse',
            'earned' => 'earned',
            'rated' => 'rated'
        );

        $mform->addElement('select', 'select_statement', get_string('select_statement', 'local_moodle_lrs_plugin'), $options);
        $mform->setType('select_statement', PARAM_ALPHA);

        $this->add_action_buttons(false, get_string('send_statement', 'local_moodle_lrs_plugin'));
    }
}

// إنشاء النموذج
$mform = new local_moodle_lrs_plugin_test_form();

$response = '';

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($fromform = $mform->get_data()) {
    $selected = $fromform->select_statement;
    $xapiSender = new XapiIntegration;
    
    // استخدام switch case لمعالجة كل اختيار
    switch ($selected) {
        case 'registered':

            $response = $xapiSender->Registered([
                'name' => 'Mahmoud Hassan',
                'email' => 'betalamoud@gmail.com',
                'duration' => 'PT30H00M00S',
                'learneMobileNo' => '+201000944804',
                'learnerFullName' => 'Mahmoud Hassan Ali Attya',
                'learnerNationality' => 'Egypt',
                'dateOfBirth' => '20/09/1988',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
            break;
        case 'initialized':
            
            $response = $xapiSender->Initialized([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
            break;
        case 'watched':
            $response = $xapiSender->Watched([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'lessonUrl'=> $CFG->wwwroot . '/mod/video/view.php?id=123',
                'lessonName'=> 'Test video',
                'lessonDesc'=> 'Video Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'completion' => true,
                'duration' => 'PT00H05M00S',
            ]);
            break;
        case 'completed':
            $response = $xapiSender->Completed([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'lessonUrl'=> $CFG->wwwroot . '/mod/lesson/view.php?id=123',
                'lessonName'=> 'Test lesson',
                'lessonDesc'=> 'Lesson Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'lessonDuration' => 'PT30H00M00S',
            ]);
            break;
        case 'completedUnit':
            $response = $xapiSender->CompletedUnit([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'unitUrl'=> $CFG->wwwroot . '/course/section.php?id=123',
                'unitName'=> 'Test unit',
                'unitDesc'=> 'Unit Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
            
            break;
        case 'progressed':
            $response = $xapiSender->Progressed([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'scaled' => 1,
                'completion' => true,
                'courseLang' => 'en-US',
            ]);
            break;
        case 'attempted':

            $response = $xapiSender->Attempted([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'quizUrl' => $CFG->wwwroot . '/mod/quiz/view.php?id=123',
                'quizName' => 'Test quiz',
                'quizDesc' => 'quiz Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'attempNumber' => '1',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'scaled' => 0.8,
                'raw' => 8,
                'min' => 5,
                'max' => 10,
                'completion' => true,
                'success' => true,
            ]);

            break;
        case 'completedCourse':
            $response = $xapiSender->CompletedCourse([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
            ]);
            break;
        case 'earned':
            $response = $xapiSender->Earned([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'certUrl' => '/path/to/certificate',
                'certName' => 'Test certificate',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
            break;
        case 'rated':
            $response = $xapiSender->Rated([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'scaled' => 0.8,
                'raw' => 4,
                'min' => 0,
                'max' => 5,
                'comment' => 'good course',
            ]);
            break;
        default:
            $response = "Invalid statement type selected.";
            break;
    }
}

// عرض الصفحة
echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, 'test');
echo $OUTPUT->heading(get_string('test_connection', 'local_moodle_lrs_plugin'));

$mform->display();

// عرض الاستجابة أسفل النموذج
if (!empty($response)) {

        if( isset($response['http_code']) ){
                echo html_writer::div('<strong>HTTP Code:</strong> ' . $response['http_code'], 'alert alert-info');

                echo html_writer::div('<strong>Response:</strong> ' . $response['response'], 'alert alert-info');
            
                if (!empty($response['error'])) {
                    echo html_writer::div('<strong>Error:</strong> ' . $response['error'], 'alert alert-danger');
                } else {

                    if( $response['http_code'] == 200){
                        $formatted_data = json_encode(json_decode($response['data_sent']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        echo html_writer::div('<strong>Data Sent:</strong><br>' . html_writer::tag('pre', htmlentities($formatted_data)), 'alert alert-info');

                    }
                }
        }else{
            echo html_writer::div($response, 'alert alert-info');
        }

}else{
    echo html_writer::div('No data', 'alert alert-info');
}

echo $OUTPUT->footer();