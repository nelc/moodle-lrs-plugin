<?php


/**
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */
 
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'بلاجن Moodle LRS';
$string['endpoint'] = 'نقطة نهاية LRS';
$string['endpoint_desc'] = 'أدخل عنوان URL لنقطة نهاية LRS.';
$string['username'] = 'اسم المستخدم';
$string['username_desc'] = 'أدخل اسم المستخدم لمصادقة LRS.';
$string['password'] = 'كلمة المرور';
$string['password_desc'] = 'أدخل كلمة المرور لمصادقة LRS.';
$string['platformname_ar'] = 'اسم المنصة (بالعربية)';
$string['platformname_ar_desc'] = 'أدخل اسم المنصة باللغة العربية.';
$string['platformname'] = 'اسم المنصة';
$string['platformname_desc'] = 'أدخل اسم المنصة.';
$string['platformname_en'] = 'اسم المنصة (بالإنجليزية)';
$string['platformname_en_desc'] = 'أدخل اسم المنصة باللغة الإنجليزية.';
$string['generalsettings'] = 'إعدادات LRS Plugin';
$string['test_connection'] = 'اختبار الاتصال';
$string['test_connection_desc'] = 'اضغط هنا لاختبار الاتصال بنقطة نهاية LRS.';
$string['select_statement'] = 'اختر statement.';
$string['send_statement'] = 'إرسال';
$string['select_statement_desc'] = 'برجاء اختيار statement لتجربة عملية الربط.';

$string['user_enrolled_message'] = 'مرحبًا {$a->firstname}! لقد تم تسجيلك بنجاح في الدورة: {$a->coursename}';
$string['lesson_completed_message'] = 'تهانينا {$a->firstname}! لقد أكملت الدرس: {$a->lessonname}';

$string['eventvideowatched'] = 'تمت مشاهدة الفيديو';

$string['fullname'] = 'اسم الدورة';
$string['course_duration_hours'] = 'مدة الدورة (ساعات)';
$string['course_duration'] = 'مدة الدورة';
$string['course_duration_minutes'] = 'مدة الدورة (دقائق)';
$string['course_language'] = 'لغة الدورة';
$string['is_nelc_enabled'] = 'تمكين الربط مع NELC';
$string['bzzix_fields_section'] = 'إعدادات أخرى';
$string['error_duration'] = 'يجب أن تكون مدة الدورة قيمة موجبة.';

$string['language_ar'] = 'العربية';
$string['language_en'] = 'الإنجليزية';
$string['language_fr'] = 'الفرنسية';
$string['language_es'] = 'الإسبانية';
$string['language_de'] = 'الألمانية';
$string['language_it'] = 'الإيطالية';
$string['language_ru'] = 'الروسية';
$string['language_zh'] = 'الصينية';
$string['language_ja'] = 'اليابانية';
$string['language_tr'] = 'التركية';
$string['language_hi'] = 'الهندية';
$string['language_pt'] = 'البرتغالية';


$string['pluginname'] = 'Moodle LRS Plugin';
$string['lesson_duration'] = 'مدة الدرس';
$string['lesson_duration_help'] = 'أدخل مدة الدرس بالساعات والدقائق';
$string['hours'] = 'ساعات';
$string['minutes'] = 'دقائق';
$string['numeric'] = 'يجب إدخال قيمة رقمية';
$string['maxminutes'] = 'يجب أن تكون الدقائق أقل من 60';