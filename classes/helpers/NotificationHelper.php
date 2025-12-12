<?php
namespace local_moodle_lrs_plugin\helpers;

/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */

defined('MOODLE_INTERNAL') || die();

class NotificationHelper {

    public static function handleResponse($response) {
        if (!empty($response)) {
            if (isset($response['http_code'])) {
                if ($response['http_code'] == 200) {
                    \core\notification::success('تم إرسال التقرير للمركز الوطني NELC !' . $response['response']);
                } else {
                    \core\notification::warning('لم يتم إرسال التقرير للمركز الوطني NELC: ' . $response['http_code']);
                    \core\notification::warning('<pre>' . print_r($response['response'], true) . '</pre>');

                }

                if (!empty($response['error'])) {
                    \core\notification::error('خطأ في إرسال البيانات: ' . $response['error']);
                }
            } else {
                \core\notification::info('لم يتم تلقي رمز استجابة، الرد: ' . json_encode($response));
            }
        } else {
            \core\notification::warning('لم يتم إرسال أي بيانات.');
        }
    }

    public static function get_course_completion_percentage($userid, $courseid) {
        global $DB;
    
        // جلب جميع الأنشطة التي تتطلب الإكمال فقط والتي لم يتم حذفها
        $sql_total = "SELECT COUNT(*) FROM {course_modules} 
                      WHERE course = :courseid AND completion > 0 
                      AND deletioninprogress = 0 AND visible = 1";
        $total_activities = $DB->count_records_sql($sql_total, ['courseid' => $courseid]);
    
        if ($total_activities == 0) {
            return 0;
        }
    
        // جلب عدد الأنشطة المكتملة للمستخدم بناءً على حالة الإكمال
        $sql_completed = "SELECT COUNT(*) FROM {course_modules_completion} WHERE coursemoduleid IN 
                          (SELECT id FROM {course_modules} 
                           WHERE course = :courseid AND completion > 0 
                           AND deletioninprogress = 0 AND visible = 1) 
                          AND userid = :userid AND completionstate IN (1, 2)";
        $completed_activities = $DB->count_records_sql($sql_completed, ['courseid' => $courseid, 'userid' => $userid]);
    
        // حساب النسبة المئوية لاكتمال الدورة
        $completion_percentage = ($completed_activities / $total_activities) * 100;
    
        return round($completion_percentage, 2);
    }
    
    public static function has_accessed_course_first_time($userid, $courseid) {
        global $DB;
    
        // التحقق مما إذا فتح المستخدم صفحة الدورة لأول مرة
        $sql = "SELECT 1 FROM {logstore_standard_log}
                WHERE userid = ? AND courseid = ? 
                AND component = 'core' AND action = 'viewed' AND target = 'course'";
    
        $first_access = $DB->record_exists_sql($sql, [$userid, $courseid]);
    
        return !$first_access; // إرجاع true إذا كان أول دخول
    }
    
    public static function is_section_completed($sectionid, $courseid, $userid) {
        global $DB;
    
        // جلب جميع الأنشطة داخل الوحدة والتي لم يتم حذفها أو إخفاؤها
        $activities = $DB->get_records_select(
            'course_modules', 
            'section = :sectionid AND course = :courseid AND deletioninprogress = 0 AND visible = 1',
            ['sectionid' => $sectionid, 'courseid' => $courseid]
        );
    
        foreach ($activities as $activity) {
            $completion = $DB->get_record('course_modules_completion', [
                'coursemoduleid' => $activity->id,
                'userid' => $userid
            ]);
    
            // إذا كان هناك نشاط غير مكتمل، فإن الوحدة غير مكتملة
            if (!$completion || $completion->completionstate != 1) {
                return false;
            }
        }
    
        return true; // الوحدة مكتملة
    }
    
    
    public static function get_section_id_from_activity($coursemoduleid) {
        global $DB;
        
        $record = $DB->get_record('course_modules', ['id' => $coursemoduleid], 'section');

        return $record ? $record->section : null;
    }


    public static function is_resource_video($resourceId) {
       global $DB;
       
       $resource = $DB->get_record('resource', array('id' => $resourceId), '*', MUST_EXIST);
       
       if (!$resource) {
           return false;
       }
       
       // الحصول على ملف المور)
       $cm = get_coursemodule_from_instance('resource', $resource->id, $resource->course);
       
       // استخدام المسار الكامل للكلاس
       $context = \context_module::instance($cm->id);
       
       $fs = get_file_storage();
       $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);
       
       if (count($files) < 1) {
           return false;
       }
       
       $file = reset($files);
       
       $mimetype = $file->get_mimetype();
        
        $video_mimetypes = array(
            'video/mp4',
            'video/webm',
            'video/ogg',
            'video/quicktime',
            'video/x-ms-wmv',
            'video/x-flv',
            'video/3gpp',
            'video/mpeg',
            'application/x-mpegURL',
            'application/dash+xml'
        );
        
        // التحقق مما إذا كان نوع الملف هو نوع فيديو
        if (in_array($mimetype, $video_mimetypes)) {
            return true;
        }
        
        // بديل: التحقق من امتداد الملف
        $filename = $file->get_filename();
        $video_extensions = array('mp4', 'webm', 'ogg', 'mov', 'wmv', 'flv', '3gp', 'mpeg', 'mpg', 'avi', 'mkv', 'm4v');
        
       foreach ($video_extensions as $ext) {
           if (substr(strtolower($filename), -strlen($ext) - 1) === '.' . $ext) {
               return true;
           }
       }
       
       return false;
    }
    
}
