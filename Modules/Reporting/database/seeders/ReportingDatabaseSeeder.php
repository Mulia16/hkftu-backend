<?php

namespace Modules\Reporting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Reporting\Models\ReportTemplate;

class ReportingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'SUBJECT_DATA',
                'name' => 'Subject Data Report',
                'format' => 'xlsx',
                'query_key' => 'subject_data',
                'parameters_json' => ['status' => 'filter by subject status'],
            ],
            [
                'code' => 'CLASS_CLASH',
                'name' => 'Class Clash Report',
                'format' => 'xlsx',
                'query_key' => 'class_clash',
                'parameters_json' => ['season_id' => 'required', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'CENTRE_COURSE',
                'name' => 'Centre Course Report',
                'format' => 'xlsx',
                'query_key' => 'centre_course',
                'parameters_json' => ['season_id' => 'required', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'QUOTA_TABLE',
                'name' => 'Quota Table Report',
                'format' => 'xlsx',
                'query_key' => 'quota_table',
                'parameters_json' => ['season_id' => 'required', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'FULL_CLASS',
                'name' => 'Full Class Report',
                'format' => 'xlsx',
                'query_key' => 'full_class',
                'parameters_json' => ['season_id' => 'optional', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'DANGER_CLASS',
                'name' => 'Danger Class Report',
                'format' => 'xlsx',
                'query_key' => 'danger_class',
                'parameters_json' => ['season_id' => 'optional', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'RECEIPT_TOTAL',
                'name' => 'Receipt Total Report',
                'format' => 'pdf',
                'query_key' => 'receipt_total',
                'parameters_json' => ['season_id' => 'optional', 'centre_id' => 'optional', 'date_from' => 'optional', 'date_to' => 'optional'],
            ],
            [
                'code' => 'COURSE_INCOME',
                'name' => 'Course Income Report',
                'format' => 'pdf',
                'query_key' => 'course_income',
                'parameters_json' => ['season_id' => 'required', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'COUPON_USAGE',
                'name' => 'Coupon Usage Report',
                'format' => 'xlsx',
                'query_key' => 'coupon_usage',
                'parameters_json' => ['season_id' => 'optional', 'date_from' => 'optional', 'date_to' => 'optional'],
            ],
            [
                'code' => 'CERTIFICATE_PRINT',
                'name' => 'Certificate Print Report',
                'format' => 'pdf',
                'query_key' => 'certificate_print',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'UNUSED_NUMBER',
                'name' => 'Unused Number Report',
                'format' => 'xlsx',
                'query_key' => 'unused_number',
                'parameters_json' => ['season_id' => 'required'],
            ],
            [
                'code' => 'COURSE_MANUSCRIPT',
                'name' => 'Course Manuscript Printout',
                'format' => 'pdf',
                'query_key' => 'course_manuscript',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'INSTRUCTOR_DATA',
                'name' => 'Instructor Data Report',
                'format' => 'pdf',
                'query_key' => 'instructor_data',
                'parameters_json' => ['instructor_id' => 'optional', 'status' => 'optional'],
            ],
            [
                'code' => 'SUBJECT_COURSE_DATA',
                'name' => 'Subject/Course Data Report',
                'format' => 'xlsx',
                'query_key' => 'subject_course_data',
                'parameters_json' => ['season_id' => 'optional', 'category_id' => 'optional'],
            ],
            [
                'code' => 'CLASSROOM_TABLE',
                'name' => 'Classroom Table',
                'format' => 'xlsx',
                'query_key' => 'classroom_table',
                'parameters_json' => ['centre_id' => 'optional'],
            ],
            [
                'code' => 'NO_PAGE_NUMBER',
                'name' => 'No Page Number Report',
                'format' => 'xlsx',
                'query_key' => 'no_page_number',
                'parameters_json' => ['season_id' => 'required'],
            ],
            [
                'code' => 'INSTRUCTOR_CONTRACT',
                'name' => 'Instructor Contract',
                'format' => 'pdf',
                'query_key' => 'instructor_contract',
                'parameters_json' => ['instructor_id' => 'required', 'season_id' => 'optional'],
            ],
            [
                'code' => 'ADVANCED_COURSE_NOTICE',
                'name' => 'Advanced Course Notice',
                'format' => 'pdf',
                'query_key' => 'advanced_course_notice',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'ADVANCED_INSTRUCTOR_NOTICE',
                'name' => 'Advanced Instructor Notice',
                'format' => 'pdf',
                'query_key' => 'advanced_instructor_notice',
                'parameters_json' => ['instructor_id' => 'required', 'season_id' => 'optional'],
            ],
            [
                'code' => 'COURSE_ANALYSIS',
                'name' => 'Course Analysis Report',
                'format' => 'xlsx',
                'query_key' => 'course_analysis',
                'parameters_json' => ['season_id' => 'required', 'centre_id' => 'optional'],
            ],
            [
                'code' => 'STUDENT_PHONE_LIST',
                'name' => 'Student Phone List',
                'format' => 'xlsx',
                'query_key' => 'student_phone_list',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'INSTRUCTOR_COMMUNICATION',
                'name' => 'Instructor Communication Table',
                'format' => 'pdf',
                'query_key' => 'instructor_communication',
                'parameters_json' => ['season_id' => 'required', 'instructor_id' => 'optional'],
            ],
            [
                'code' => 'INSTRUCTOR_SIGN_IN',
                'name' => 'Instructor Sign-In Sheet',
                'format' => 'pdf',
                'query_key' => 'instructor_sign_in',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'CERTIFICATE_APPLICATION',
                'name' => 'Certificate Application Form',
                'format' => 'pdf',
                'query_key' => 'certificate_application',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'ATTENDANCE_SHEET',
                'name' => 'Attendance Sheet',
                'format' => 'pdf',
                'query_key' => 'attendance_sheet',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'NAME_LABELS',
                'name' => 'Name Labels / Student Cards',
                'format' => 'pdf',
                'query_key' => 'name_labels',
                'parameters_json' => ['class_id' => 'required'],
            ],
            [
                'code' => 'INSTRUCTOR_PAYMENT_SLIP',
                'name' => 'Instructor Payment Slip',
                'format' => 'pdf',
                'query_key' => 'instructor_payment_slip',
                'parameters_json' => ['instructor_id' => 'required', 'month' => 'required'],
            ],
            [
                'code' => 'INSTRUCTOR_CHEQUE',
                'name' => 'Instructor Cheque',
                'format' => 'pdf',
                'query_key' => 'instructor_cheque',
                'parameters_json' => ['instructor_id' => 'required', 'month' => 'required'],
            ],
            [
                'code' => 'INSTRUCTOR_FEE_SUMMARY',
                'name' => 'Instructor Fee Summary',
                'format' => 'xlsx',
                'query_key' => 'instructor_fee_summary',
                'parameters_json' => ['season_id' => 'required', 'instructor_id' => 'optional'],
            ],
            [
                'code' => 'QUARTERLY_ANALYSIS',
                'name' => 'Quarterly Course Analysis',
                'format' => 'xlsx',
                'query_key' => 'quarterly_analysis',
                'parameters_json' => ['year' => 'required', 'quarter' => 'required'],
            ],
            [
                'code' => 'ANNUAL_TAX_EXPORT',
                'name' => 'Annual Tax Export',
                'format' => 'xlsx',
                'query_key' => 'annual_tax_export',
                'parameters_json' => ['year' => 'required', 'instructor_id' => 'optional'],
            ],
        ];

        foreach ($templates as $template) {
            ReportTemplate::firstOrCreate(
                ['code' => $template['code']],
                $template
            );
        }
    }
}
