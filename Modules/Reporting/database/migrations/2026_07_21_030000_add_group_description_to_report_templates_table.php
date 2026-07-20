<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporting.report_templates', function (Blueprint $table) {
            $table->string('group')->default('general')->after('name');
            $table->text('description')->nullable()->after('group');
        });

        $groups = [
            'SUBJECT_DATA' => ['group' => 'Course Planning', 'description' => 'Inspect subject data, fees, material fees, and fee increases.'],
            'UNUSED_NUMBER' => ['group' => 'Course Planning', 'description' => 'Inspect unused course and subject numbers for the season.'],
            'COURSE_MANUSCRIPT' => ['group' => 'Course Planning', 'description' => 'Print course text and manuscript for brochure or review.'],
            'INSTRUCTOR_DATA' => ['group' => 'Course Planning', 'description' => 'Print instructor profile and qualification information.'],
            'SUBJECT_COURSE_DATA' => ['group' => 'Course Planning', 'description' => 'Print combined subject and course information by season.'],
            'NO_PAGE_NUMBER' => ['group' => 'Course Planning', 'description' => 'List courses missing brochure page numbers.'],
            'CLASS_CLASH' => ['group' => 'Scheduling', 'description' => 'List schedule conflicts between classes, rooms, and instructors.'],
            'CENTRE_COURSE' => ['group' => 'Scheduling', 'description' => 'Courses offered per centre for the season.'],
            'CLASSROOM_TABLE' => ['group' => 'Scheduling', 'description' => 'Classroom usage and availability status.'],
            'QUOTA_TABLE' => ['group' => 'Registration', 'description' => 'Course quota and seat availability overview.'],
            'COURSE_ANALYSIS' => ['group' => 'Registration', 'description' => 'Registration trends and course demand analysis.'],
            'RECEIPT_TOTAL' => ['group' => 'Registration', 'description' => 'Total receipts by centre and period.'],
            'COUPON_USAGE' => ['group' => 'Registration', 'description' => 'Coupon usage summary mapped to receipts.'],
            'FULL_CLASS' => ['group' => 'Registration', 'description' => 'Classes that have reached full capacity.'],
            'DANGER_CLASS' => ['group' => 'Registration', 'description' => 'Classes below minimum enrolment threshold.'],
            'INSTRUCTOR_CONTRACT' => ['group' => 'Pre-Class', 'description' => 'Generate instructor contract document.'],
            'ADVANCED_COURSE_NOTICE' => ['group' => 'Pre-Class', 'description' => 'Notify students about advanced course enrolment.'],
            'ADVANCED_INSTRUCTOR_NOTICE' => ['group' => 'Pre-Class', 'description' => 'Notify instructor about upcoming class assignment.'],
            'STUDENT_PHONE_LIST' => ['group' => 'Pre-Class', 'description' => 'Contact list for postponed or cancelled courses.'],
            'INSTRUCTOR_COMMUNICATION' => ['group' => 'Pre-Class', 'description' => 'Pre-class instructor communication tracking report.'],
            'INSTRUCTOR_SIGN_IN' => ['group' => 'Pre-Class', 'description' => 'Instructor sign-in sheet for class sessions.'],
            'NAME_LABELS' => ['group' => 'Pre-Class', 'description' => 'Printable student name labels and cards.'],
            'ATTENDANCE_SHEET' => ['group' => 'In-Class', 'description' => 'Attendance input sheet for class sessions.'],
            'CERTIFICATE_APPLICATION' => ['group' => 'Completion', 'description' => 'Certificate application form with eligibility check.'],
            'CERTIFICATE_PRINT' => ['group' => 'Completion', 'description' => 'Batch certificate printout for completed courses.'],
            'COURSE_INCOME' => ['group' => 'Finance', 'description' => 'Income report for selected course or class.'],
            'INSTRUCTOR_PAYMENT_SLIP' => ['group' => 'Finance', 'description' => 'Instructor support payment order slip.'],
            'INSTRUCTOR_CHEQUE' => ['group' => 'Finance', 'description' => 'Cheque printing output for instructor payments.'],
            'INSTRUCTOR_FEE_SUMMARY' => ['group' => 'Finance', 'description' => 'Fee summary by instructor and season.'],
            'QUARTERLY_ANALYSIS' => ['group' => 'Quarterly/Annual', 'description' => 'End-of-quarter course and registration analysis.'],
            'ANNUAL_TAX_EXPORT' => ['group' => 'Quarterly/Annual', 'description' => 'Annual tax data export in Excel format.'],
        ];

        foreach ($groups as $code => $data) {
            DB::table('reporting.report_templates')
                ->where('code', $code)
                ->update($data);
        }
    }

    public function down(): void
    {
        Schema::table('reporting.report_templates', function (Blueprint $table) {
            $table->dropColumn(['group', 'description']);
        });
    }
};
