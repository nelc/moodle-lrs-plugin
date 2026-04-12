<?php
/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */

namespace local_moodle_lrs_plugin;

defined('MOODLE_INTERNAL') || die();

use local_moodle_lrs_plugin\helpers\NotificationHelper;
use local_moodle_lrs_plugin\Interactions\XapiIntegration;

class observer {

    // معالجة حدث اشتراك المستخدم في الدورة.    
    public static function user_enrolment_created($event)
    {
        global $DB, $USER;
    
        // جلب بيانات المستخدم المُسجل في الدورة
        $user = $DB->get_record('user', ['id' => $event->relateduserid], '*', MUST_EXIST);
    
        $mobile = $DB->get_field_sql("
            SELECT uid.data 
            FROM {user_info_data} uid
            JOIN {user_info_field} uif ON uid.fieldid = uif.id
            WHERE uif.shortname = 'mobile' AND uid.userid = ?", 
            [$user->id]);

        $nationality = $DB->get_field_sql("
            SELECT uid.data 
            FROM {user_info_data} uid
            JOIN {user_info_field} uif ON uid.fieldid = uif.id
            WHERE uif.shortname = 'nationality' AND uid.userid = ?", 
            [$user->id]);

        $dob = $DB->get_field_sql("
            SELECT uid.data 
            FROM {user_info_data} uid
            JOIN {user_info_field} uif ON uid.fieldid = uif.id
            WHERE uif.shortname = 'dob' AND uid.userid = ?", 
            [$user->id]);
            
        // جلب بيانات الدورة التدريبية
        $course = $DB->get_record('course', ['id' => $event->courseid], '*', MUST_EXIST);
        
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }

        $hours = 0;
        $minutes = 0;
        if (isset($course->course_duration)) {
            $hours = floor($course->course_duration / 60);
            $minutes = $course->course_duration % 60;
        }
        // تنسيق المدة بالطريقة المطلوبة
        $duration = sprintf('PT%02dH%02dM00S', $hours, $minutes);
        
        $courseLang = $course->course_language ?? 'en-US';
        
        $instructor = $DB->get_record_sql("
            SELECT u.*
            FROM {user} u
            JOIN {role_assignments} ra ON u.id = ra.userid
            JOIN {context} c ON ra.contextid = c.id
            WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
            LIMIT 1
        ", ['courseid' => $course->id]);
    
        $xapiSender = new XapiIntegration;
        $response = $xapiSender->Registered([
            'name'       => NotificationHelper::get_actor_name($user->id),
            'email'      => $user->email,
            'duration' => $duration,
            'learneMobileNo' => $mobile ?: '',
            'learnerFullName' => fullname($user),
            'learnerNationality' => $nationality ?: '',
            'dateOfBirth' => $dob ? date("d/m/Y", strtotime($dob)) : '',
            'instructor' => $instructor ? fullname($instructor) : 'غير معروف',
            'inst_email' => $instructor ? $instructor->email : 'unknow@mail.com',
            'courseId'   => $course->id,
            'courseName' => $course->fullname,
            'courseDesc' => $course->summary,
            'courseLang' => $courseLang,
        ]);

        NotificationHelper::handleResponse($response);

         $xapiSender2 = new XapiIntegration;
         $response2 = $xapiSender2->Initialized([
             'name' => NotificationHelper::get_actor_name($user->id),
             'email' => $user->email,
             'instructor' => $instructor ? fullname($instructor) : '',
             'inst_email' => $instructor ? $instructor->email : 'unknow@mail.com',
             'courseId' => $course->id,
             'courseName' => $course->fullname,
             'courseDesc' => $course->summary,
             'courseLang' => $courseLang,
         ]);

         NotificationHelper::handleResponse($response2);
    }

    public static function course_activity_completion_updated($event)
    {
        global $DB, $CFG;

        $userid = $event->userid;
        $courseid = $event->courseid;
        $coursemoduleid = $event->contextinstanceid;
        $completionstate = $event->other['completionstate'];
        
        $user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename');
        $username = NotificationHelper::get_actor_name($userid);
        $useremail = $user->email;
    
        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, summary, is_nelc_enabled, course_language');
        $coursename = $course->fullname;
        $coursedesc = $course->summary;
    
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        
        $courseLang = $course->course_language ?? 'en-US';
        
        $modinfo = get_fast_modinfo($courseid);
        $cm = $modinfo->get_cm($coursemoduleid);
        $activityname = $cm->name;
        $activityurl = $CFG->wwwroot . "/mod/{$cm->modname}/view.php?id={$coursemoduleid}";
    
        $instanceid = $cm->instance;

        $instructor = $DB->get_record_sql("
        SELECT u.*
        FROM {user} u
        JOIN {role_assignments} ra ON u.id = ra.userid
        JOIN {context} c ON ra.contextid = c.id
        WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
        LIMIT 1
        ", ['courseid' => $course->id]);
        $inst_name = $instructor ? fullname($instructor) : 'غير معروف';
        $inst_email = $instructor && !empty($instructor->email) ? $instructor->email : 'unknown@mail.com';        
    

        if( ($cm->modname == 'lesson' || $cm->modname == 'resource') && $completionstate == 1){

            $lesson = $DB->get_record($cm->modname, ['id' => $instanceid]);

            $is_video = NotificationHelper::is_resource_video($lesson->id);
            if( !$is_video ){

                $hours = 0;
                $minutes = 0;
                if (isset($lesson->lesson_duration)) {
                    $hours = floor($lesson->lesson_duration / 60);
                    $minutes = $lesson->lesson_duration % 60;
                }
                // تنسيق المدة
                $lessonDuration = sprintf('PT%02dH%02dM00S', $hours, $minutes);

                $xapiSender = new XapiIntegration;
                $response = $xapiSender->Completed([
                    'name' => $username,
                    'email' => $useremail,
                    'lessonUrl' => $activityurl,
                    'lessonName' => $activityname,
                    'lessonDesc' => "Activity '{$activityname}' in course '{$coursename}'",
                    'instructor' => $inst_name,
                    'inst_email' => $inst_email,
                    'courseId' => $courseid,
                    'courseName' => $coursename,
                    'courseDesc' => $coursedesc,
                    'courseLang' => $courseLang,
                    'lessonDuration' => $lessonDuration,
                ]);

                NotificationHelper::handleResponse($response);

            }
        }
        // إرسال تقدم الدورة
        $completion_rate = NotificationHelper::get_course_completion_percentage($userid, $courseid);
        $xapiSender2 = new XapiIntegration;
        $response2 = $xapiSender2->Progressed([
            'name' => $username,
            'email' => $useremail,
            'courseId' => $courseid,
            'courseName' => $coursename,
            'courseDesc' => $coursedesc,
            'courseLang' => $courseLang,
            'instructor' => $inst_name,
            'inst_email' => $inst_email,
            'scaled' => $completion_rate > 0 ? round($completion_rate / 100, 2) : 0,
            'completion' => $completion_rate == 100 ? true : false,
        ]);
        NotificationHelper::handleResponse($response2);

        // إرسال إكمال الوحدة
        $sectionid = NotificationHelper::get_section_id_from_activity($coursemoduleid);
        if ($sectionid) {
            $secTCompleted = NotificationHelper::is_section_completed($sectionid, $courseid, $userid);
        
            if ($secTCompleted) {
                $section = $DB->get_record('course_sections', ['id' => $sectionid], 'name, summary');
                
                $xapiSender3 = new XapiIntegration;
                $response3 = $xapiSender3->CompletedUnit([
                    'name' => $username,
                    'email' => $useremail,
                    'unitUrl' => $CFG->wwwroot . '/course/section.php?id=' . $sectionid,
                    'unitName' => $section ? $section->name : 'وحدة غير معروفة',
                    'unitDesc' => $section ? strip_tags($section->summary) : 'لا يوجد وصف لهذه الوحدة',
                    'instructor' => $inst_name,
                    'inst_email' => $inst_email,
                    'courseId' => $courseid,
                    'courseName' => $DB->get_field('course', 'fullname', ['id' => $courseid]),
                    'courseDesc' => $DB->get_field('course', 'summary', ['id' => $courseid]),
                    'courseLang' => $courseLang,
                ]);
        
                NotificationHelper::handleResponse($response3);
            }
        }       
        

        // إرسال إكمال الدورة
        if( $completion_rate >= 100){
            $xapiSender22 = new XapiIntegration;
            $response22 = $xapiSender22->CompletedCourse([
                'name' => $username,
                'email' => $useremail,
                'courseId' => $courseid,
                'courseName' => $coursename,
                'courseDesc' => $coursedesc,
                'courseLang' => $courseLang,
                'instructor' => $inst_name,
                'inst_email' => $inst_email,
            ]);
    
            NotificationHelper::handleResponse($response22);
        }
    }

    public static function quiz_attempt_submitted($event)
    {
        global $DB, $CFG;
    
        // جلب بيانات المستخدم
        $user = $DB->get_record('user', ['id' => $event->relateduserid], '*', MUST_EXIST);
    
        // جلب بيانات المحاولة
        $attempt = $DB->get_record('quiz_attempts', ['id' => $event->objectid], '*', MUST_EXIST);
    
        $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
    
        // جلب بيانات الدورة
        $course = $DB->get_record('course', ['id' => $quiz->course], '*', MUST_EXIST);
    
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        
        $courseLang = $course->course_language ?? 'en-US';

        // جلب بيانات المحاضر (المدرب)
        $instructor = $DB->get_record_sql("
            SELECT u.*
            FROM {user} u
            JOIN {role_assignments} ra ON u.id = ra.userid
            JOIN {context} c ON ra.contextid = c.id
            WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
            LIMIT 1
        ", ['courseid' => $course->id]);
    
        // حساب الدرجة النهائية كنسبة مئوية
        $scaled_score = ($attempt->sumgrades / $quiz->grade);
    
        // إرسال البيانات إلى xAPI
        $xapiSender = new XapiIntegration;
        $response = $xapiSender->Attempted([
            'name'        => NotificationHelper::get_actor_name($user->id),
            'email'       => $user->email,
            'quizUrl'     => $CFG->wwwroot . '/mod/quiz/view.php?id=' . $quiz->id,
            'quizName'    => $quiz->name,
            'quizDesc'    => $quiz->intro,
            'instructor'  => $instructor ? fullname($instructor) : 'غير معروف',
            'inst_email'  => $instructor ? $instructor->email : 'unknown@mail.com',
            'attempNumber'=> $attempt->attempt,
            'courseId'    => $course->id,
            'courseName'  => $course->fullname,
            'courseDesc'  => $course->summary,
            'courseLang' => $courseLang,
            'scaled'      => floatval($scaled_score),  // نسبة النجاح
            'raw'         => floatval($attempt->sumgrades), // الدرجة الفعلية
            'min'         => $quiz->grade > 0 ? $quiz->grade / 2 : 0, // الحد الأدنى للدرجة
            'max'         => floatval($quiz->grade), // الحد الأقصى للدرجة
            'completion'  => ($attempt->state == 'finished') ? true : false, 
            'success'     => ($scaled_score >= 0.5) ? true : false,
        ]);
    
        NotificationHelper::handleResponse($response);
    }
    
    public static function course_completed($event)
    {
        global $DB, $CFG;

        $userid = $event->userid;
        $courseid = $event->courseid;

        $user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename');
        $username = NotificationHelper::get_actor_name($userid);
        $useremail = $user->email;
    
        // جلب بيانات الدورة
        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, summary, is_nelc_enabled, course_language');
        $coursename = $course->fullname;
        $coursedesc = $course->summary;

        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        
        $courseLang = $course->course_language ?? 'en-US';

        $instructor = $DB->get_record_sql("
        SELECT u.*
        FROM {user} u
        JOIN {role_assignments} ra ON u.id = ra.userid
        JOIN {context} c ON ra.contextid = c.id
        WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
        LIMIT 1
        ", ['courseid' => $course->id]);
        $inst_name = $instructor ? fullname($instructor) : 'غير معروف';
        $inst_email = $instructor && !empty($instructor->email) ? $instructor->email : 'unknown@mail.com';        


        $xapiSender = new XapiIntegration;
        $response = $xapiSender->CompletedCourse([
            'name' => $username,
            'email' => $useremail,
            'courseId' => $courseid,
            'courseName' => $coursename,
            'courseDesc' => $coursedesc,
            'courseLang' => $courseLang,
            'instructor' => $inst_name,
            'inst_email' => $inst_email,
        ]);

        NotificationHelper::handleResponse($response);
    }
    
    public static function course_reviewed($event)
    {
        global $DB;
    
        // استخراج البيانات من الحدث
        $userid = $event->userid;
        $courseid = $event->courseid;
        $feedback_id = $event->objectid;
    
        // جلب معلومات الدورة
        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, summary, is_nelc_enabled, course_language');
    
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        
        $courseLang = $course->course_language ?? 'en-US';

        // جلب معلومات المستخدم
        $user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename');
        // جلب بيانات المحاضر (المدرب)
        $instructor = $DB->get_record_sql("
        SELECT u.*
        FROM {user} u
        JOIN {role_assignments} ra ON u.id = ra.userid
        JOIN {context} c ON ra.contextid = c.id
        WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
        LIMIT 1
        ", ['courseid' => $course->id]);
        $inst_name = $instructor ? fullname($instructor) : 'غير معروف';
        $inst_email = $instructor ? $instructor->email : 'unknown@mail.com';
        // استرجاع جميع الإجابات المرتبطة بهذا التقييم
        $feedback_values = $DB->get_records('feedback_value', ['completed' => $feedback_id]);

        // تعيين القيم الافتراضية
        $scaled = 0.0;
        $raw = 0;
        $min = 0;
        $max = 5;
        $comment = 'No comment provided';

        // التحقق من وجود بيانات التقييم
        if (!empty($feedback_values)) {
            $sum = 0;
            $count = 0;
            
            foreach ($feedback_values as $value) {
                // إذا كانت القيمة رقمية، فاحسب متوسط التقييم
                if (is_numeric($value->value)) {
                    $sum += $value->value;
                    $count++;
                }
                // إذا كانت القيمة نصية، فهي على الأغلب تعليق
                elseif (is_string($value->value) && !is_numeric($value->value)) {
                    $comment = $value->value;
                }
            }

            if ($count > 0) {
                $raw = round($sum / $count); // متوسط التقييم
                $scaled = $raw / $max; // تحويل التقييم إلى نسبة مئوية بين 0 و 1
            }
        }

        // إرسال بيان xAPI
        $xapiSender = new XapiIntegration;
        $response = $xapiSender->Rated([
            'name' => NotificationHelper::get_actor_name($user->id),
            'email' => $user->email,
            'courseId' => $course->id,
            'courseName' => $course->fullname,
            'courseDesc' => $course->summary,
            'courseLang' => $courseLang,
            'instructor'  => $inst_name,
            'inst_email'  => $inst_email,
            'scaled' => $scaled,
            'raw' => $raw,
            'min' => $min,
            'max' => $max,
            'comment' => $comment,
        ]);
    
        // التعامل مع الاستجابة
        NotificationHelper::handleResponse($response);

    }
    
    public static function rating_created($event)
    {
        global $DB, $CFG;
        
        // استخراج بيانات التقييم
        $rating = $event->other['rating']; // التقييم الفعلي
        $courseid = $event->contextinstanceid;
        // جلب بيانات الدورة التدريبية
        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, summary, course_language, is_nelc_enabled');
        
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        $courseLang = $course->course_language ?? 'en-US';
        // جلب بيانات المستخدم
        $user = $DB->get_record('user', ['id' => $event->userid], 'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename');
    
        $instructor = $DB->get_record_sql("
        SELECT u.*
        FROM {user} u
        JOIN {role_assignments} ra ON u.id = ra.userid
        JOIN {context} c ON ra.contextid = c.id
        WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
        LIMIT 1
        ", ['courseid' => $course->id]);
        $inst_name = $instructor ? fullname($instructor) : 'غير معروف';
        $inst_email = $instructor ? $instructor->email : 'unknown@mail.com';
    
        $comment = "No comment"; // القيمة الافتراضية

        // التحقق مما إذا كان هناك سجل متاح
        if ($event->objecttable === 'tool_courserating_rating' && !empty($event->objectid)) {
            $record = $event->get_record_snapshot('tool_courserating_rating', $event->objectid);
            if (!empty($record->review)) {
                $comment = $record->review;
            }
        }

        // تجهيز البيانات وإرسالها
        $xapiSender = new XapiIntegration;
        $response = $xapiSender->Rated([
            'name' => NotificationHelper::get_actor_name($user->id),
            'email' => $user->email,
            'courseId' => $course->id,
            'courseName' => $course->fullname,
            'courseDesc' => $course->summary,
            'courseLang' => $courseLang,
            'instructor' => $inst_name,
            'inst_email' => $inst_email,
            'scaled' => $rating / 5,
            'raw' => $rating,
            'min' => 0,
            'max' => 5,
            'comment' => $comment,
        ]);
    
        NotificationHelper::handleResponse($response);
    }

    public static function rating_updated($event)
    {
        global $DB, $CFG;
        
        // استخراج بيانات التقييم
        $rating = $event->other['rating']; // التقييم الفعلي
        $courseid = $event->contextinstanceid; // معرف الكورس
        // جلب بيانات الدورة التدريبية
        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, summary, is_nelc_enabled, course_language');
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        $courseLang = $course->course_language ?? 'en-US';
        // جلب بيانات المستخدم
        $user = $DB->get_record('user', ['id' => $event->userid], 'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename');
    
        $instructor = $DB->get_record_sql("
        SELECT u.*
        FROM {user} u
        JOIN {role_assignments} ra ON u.id = ra.userid
        JOIN {context} c ON ra.contextid = c.id
        WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
        LIMIT 1
        ", ['courseid' => $course->id]);
        $inst_name = $instructor ? fullname($instructor) : 'غير معروف';
        $inst_email = $instructor ? $instructor->email : 'unknown@mail.com';
    
        $comment = "No comment"; // القيمة الافتراضية

        // التحقق مما إذا كان هناك سجل متاح
        if ($event->objecttable === 'tool_courserating_rating' && !empty($event->objectid)) {
            $record = $event->get_record_snapshot('tool_courserating_rating', $event->objectid);
            if (!empty($record->review)) {
                $comment = $record->review;
            }
        }

        // تجهيز البيانات وإرسالها
        $xapiSender = new XapiIntegration;
        $response = $xapiSender->Rated([
            'name' => NotificationHelper::get_actor_name($user->id),
            'email' => $user->email,
            'courseId' => $course->id,
            'courseName' => $course->fullname,
            'courseDesc' => $course->summary,
            'courseLang' => $courseLang,
            'instructor' => $inst_name,
            'inst_email' => $inst_email,
            'scaled' => $rating / 5, // تحويل التقييم إلى نسبة مئوية (من 0 إلى 1)
            'raw' => $rating,
            'min' => 0,
            'max' => 5,
            'comment' => $comment,
        ]);
    
        NotificationHelper::handleResponse($response);
    }
    
    public static function video_watched($event) {
        global $DB, $CFG;
    
        // استخراج بيانات الحدث
        $data = $event->get_data();
        
        // استخراج المعلومات المطلوبة
        $userId = $data['userid'];
        $courseId = $data['courseid'] ?? ($data['other']['courseid'] ?? null);
        $videoId = $data['objectid']; // معرف الفيديو في `video_logs`
        $duration = !empty($data['other']['duration']) ? $data['other']['duration'] : 'PT00H00M00S';

        // جلب بيانات المستخدم من قاعدة البيانات
        $user = $DB->get_record('user', ['id' => $userId], 'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename');
        $username = NotificationHelper::get_actor_name($userId);
        $useremail = $user->email;
    
        // جلب بيانات الدورة التدريبية
        // جلب بيانات الدورة
        $course = $DB->get_record('course', ['id' => $courseId], 'id, fullname, summary, is_nelc_enabled, course_language');
        if (!$course || !$course->is_nelc_enabled) {
            return;
        }
        $courseLang = $course->course_language ?? 'en-US';
        $coursename = $course->fullname;
        $coursedesc = $course->summary;
    
        $modinfo = get_fast_modinfo($courseId);
        $cm = $modinfo->get_cm($videoId);
        $activityname = $cm->name;
        $activitydesc= $cm->summary ?? '';
        $activityurl = $CFG->wwwroot . "/mod/{$cm->modname}/view.php?id={$videoId}";
    
        // جلب بيانات المعلم (صاحب الدورة)
        $instructor = $DB->get_record_sql("
        SELECT u.*
        FROM {user} u
        JOIN {role_assignments} ra ON u.id = ra.userid
        JOIN {context} c ON ra.contextid = c.id
        WHERE c.instanceid = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
        LIMIT 1
        ", ['courseid' => $course->id]);
        $inst_name = $instructor ? fullname($instructor) : 'غير معروف';
        $inst_email = $instructor && !empty($instructor->email) ? $instructor->email : 'unknown@mail.com';        
        
        // إرسال الحدث إلى xAPI
        $xapiSender = new XapiIntegration();
        $response = $xapiSender->Watched([
            'name'        => $username, 
            'email'       => $useremail,
            'lessonUrl'   => $activityurl,
            'lessonName'  => $activityname,
            'lessonDesc'  => $activitydesc,
            'instructor'  => $inst_name,
            'inst_email'  => $inst_email,
            'courseId'    => $course->id,
            'courseName'  => $coursename,
            'courseDesc'  => $coursedesc,
            'courseLang' => $courseLang,
            'completion'  => true,
            'duration'    => $duration
        ]);
    
        NotificationHelper::handleResponse($response);
    }
    
    
}

