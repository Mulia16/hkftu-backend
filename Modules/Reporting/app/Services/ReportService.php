<?php

namespace Modules\Reporting\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Reporting\Exports\ReportExport;
use Modules\Reporting\Jobs\ProcessReportRun;
use Modules\Reporting\Models\ReportRun;
use Modules\Reporting\Models\ReportTemplate;

class ReportService
{
    public function __construct(
        private ReportPdfService $pdfService,
    ) {}

    public function run(int $templateId, int $userId, ?array $parameters = null): ReportRun
    {
        $run = ReportRun::create([
            'template_id' => $templateId,
            'requested_by' => $userId,
            'parameters_json' => $parameters,
            'status' => 'queued',
        ]);

        ProcessReportRun::dispatch($run->id);

        return $run->fresh();
    }

    public function executeReport(ReportRun $run): void
    {
        $run->update(['status' => 'running', 'started_at' => now()]);

        try {
            $template = $run->template;
            $params = $run->parameters_json ?? [];

            $data = match ($template->query_key) {
                'receipt_total' => $this->queryReceiptTotal($params),
                'full_class' => $this->queryFullClass($params),
                'danger_class' => $this->queryDangerClass($params),
                'subject_data' => $this->querySubjectData($params),
                'quota_table' => $this->queryQuotaTable($params),
                'class_clash' => $this->queryClassClash($params),
                'centre_course' => $this->queryCentreCourse($params),
                'course_income' => $this->queryCourseIncome($params),
                'coupon_usage' => $this->queryCouponUsage($params),
                'certificate_print' => $this->queryCertificatePrint($params),
                'unused_number' => $this->queryUnusedNumber($params),
                'course_manuscript' => $this->queryCourseManuscript($params),
                'instructor_data' => $this->queryInstructorData($params),
                'subject_course_data' => $this->querySubjectCourseData($params),
                'classroom_table' => $this->queryClassroomTable($params),
                'no_page_number' => $this->queryNoPageNumber($params),
                'instructor_contract' => $this->queryInstructorContract($params),
                'advanced_course_notice' => $this->queryAdvancedCourseNotice($params),
                'advanced_instructor_notice' => $this->queryAdvancedInstructorNotice($params),
                'course_analysis' => $this->queryCourseAnalysis($params),
                'student_phone_list' => $this->queryStudentPhoneList($params),
                'instructor_communication' => $this->queryInstructorCommunication($params),
                'instructor_sign_in' => $this->queryInstructorSignIn($params),
                'certificate_application' => $this->queryCertificateApplication($params),
                'attendance_sheet' => $this->queryAttendanceSheet($params),
                'name_labels' => $this->queryNameLabels($params),
                'instructor_payment_slip' => $this->queryInstructorPaymentSlip($params),
                'instructor_cheque' => $this->queryInstructorCheque($params),
                'instructor_fee_summary' => $this->queryInstructorFeeSummary($params),
                'quarterly_analysis' => $this->queryQuarterlyAnalysis($params),
                'annual_tax_export' => $this->queryAnnualTaxExport($params),
                default => throw new \InvalidArgumentException("Unknown report query_key: {$template->query_key}"),
            };

            $isXlsx = $template->format === 'xlsx';
            $isPdf = $template->format === 'pdf';
            $printableReports = ['attendance_sheet', 'name_labels', 'instructor_sign_in', 'certificate_application', 'certificate_print', 'instructor_contract', 'advanced_course_notice', 'advanced_instructor_notice', 'instructor_communication', 'instructor_cheque'];

            if ($isPdf && in_array($template->query_key, $printableReports)) {
                $path = $this->generatePrintablePdf($template->query_key, $params, $data);
            } elseif ($isXlsx) {
                $ext = 'xlsx';
                $filename = $template->query_key . '_' . $run->id . '_' . now()->format('YmdHis') . '.' . $ext;
                $path = 'reports/' . $filename;
                Excel::store(new ReportExport($data), $path, 'local');
            } else {
                $ext = 'csv';
                $filename = $template->query_key . '_' . $run->id . '_' . now()->format('YmdHis') . '.' . $ext;
                $path = 'reports/' . $filename;
                Storage::disk('local')->put($path, $this->generateCsv($data));
            }

            $run->update([
                'status' => 'completed',
                'file_path' => $path,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }

    private function applyCentreFilter($query, array $params, string $table = 'class_scheduling.classes')
    {
        if (!empty($params['centre_id'])) {
            $query->where("{$table}.centre_id", $params['centre_id']);
        }
        return $query;
    }

    private function applySeasonFilter($query, array $params, string $table = 'course_catalogue.courses')
    {
        if (!empty($params['season_id'])) {
            $query->where("{$table}.season_id", $params['season_id']);
        }
        return $query;
    }

    private function queryReceiptTotal(array $params): Collection
    {
        $query = DB::table('payment.receipts')
            ->join('enrolment.enrolments', 'payment.receipts.enrolment_id', '=', 'enrolment.enrolments.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->select(
                'class_scheduling.centres.name as centre',
                'course_catalogue.subjects.name as subject',
                DB::raw('SUM(payment.receipts.amount) as total_amount'),
                DB::raw('COUNT(payment.receipts.id) as receipt_count')
            )
            ->groupBy('class_scheduling.centres.name', 'course_catalogue.subjects.name');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        if (!empty($params['date_from'])) {
            $query->where('payment.receipts.issued_at', '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->where('payment.receipts.issued_at', '<=', $params['date_to']);
        }

        return $query->get();
    }

    private function queryFullClass(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('enrolment.enrolments', function ($join) {
                $join->on('class_scheduling.classes.id', '=', 'enrolment.enrolments.class_id')
                    ->where('enrolment.enrolments.status', '=', 'confirmed');
            })
            ->select(
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.capacity',
                DB::raw('COUNT(enrolment.enrolments.id) as confirmed_count')
            )
            ->groupBy(
                'class_scheduling.classes.id',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.capacity'
            )
            ->havingRaw('COUNT(enrolment.enrolments.id) >= class_scheduling.classes.capacity');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function queryDangerClass(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('enrolment.enrolments', function ($join) {
                $join->on('class_scheduling.classes.id', '=', 'enrolment.enrolments.class_id')
                    ->where('enrolment.enrolments.status', '=', 'confirmed');
            })
            ->select(
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.min_students',
                'class_scheduling.classes.capacity',
                DB::raw('COUNT(enrolment.enrolments.id) as confirmed_count')
            )
            ->groupBy(
                'class_scheduling.classes.id',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.min_students',
                'class_scheduling.classes.capacity'
            )
            ->havingRaw('COUNT(enrolment.enrolments.id) < class_scheduling.classes.min_students');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function querySubjectData(array $params): Collection
    {
        $query = DB::table('course_catalogue.subjects')
            ->select(
                'course_catalogue.subjects.subject_code',
                'course_catalogue.subjects.name',
                'course_catalogue.subjects.tuition_fee',
                'course_catalogue.subjects.material_fee',
                'course_catalogue.subjects.instructor_fee_default',
                'course_catalogue.subjects.total_hours',
                'course_catalogue.subjects.lesson_hours',
                'course_catalogue.subjects.status'
            );

        if (!empty($params['status'])) {
            $query->where('course_catalogue.subjects.status', $params['status']);
        }

        return $query->get();
    }

    private function queryQuotaTable(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('enrolment.enrolments', function ($join) {
                $join->on('class_scheduling.classes.id', '=', 'enrolment.enrolments.class_id')
                    ->where('enrolment.enrolments.status', '=', 'confirmed');
            })
            ->select(
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.capacity',
                DB::raw('COUNT(enrolment.enrolments.id) as enrolled_count'),
                DB::raw('class_scheduling.classes.capacity - COUNT(enrolment.enrolments.id) as remaining')
            )
            ->groupBy(
                'class_scheduling.classes.id',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.capacity'
            );

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function queryClassClash(array $params): Collection
    {
        $query = DB::table('class_scheduling.clash_check_results')
            ->join('class_scheduling.classes', 'class_scheduling.clash_check_results.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->select(
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'class_scheduling.clash_check_results.severity',
                'class_scheduling.clash_check_results.check_type',
                'class_scheduling.clash_check_results.message',
                'class_scheduling.clash_check_results.created_at'
            )
            ->orderByDesc('class_scheduling.clash_check_results.created_at');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function queryCentreCourse(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->select(
                'class_scheduling.centres.name as centre',
                'class_scheduling.centres.code as centre_code',
                'course_catalogue.subjects.name as subject',
                'course_catalogue.courses.course_code',
                'class_scheduling.classes.class_code',
                'class_scheduling.classes.capacity',
                'class_scheduling.classes.status'
            )
            ->orderBy('class_scheduling.centres.name')
            ->orderBy('course_catalogue.subjects.name');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function queryCourseIncome(array $params): Collection
    {
        $query = DB::table('payment.receipts')
            ->join('enrolment.enrolments', 'payment.receipts.enrolment_id', '=', 'enrolment.enrolments.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->select(
                'course_catalogue.courses.course_code',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.centres.name as centre',
                DB::raw('COUNT(DISTINCT enrolment.enrolments.id) as enrolment_count'),
                DB::raw('SUM(payment.receipts.amount) as total_income')
            )
            ->groupBy(
                'course_catalogue.courses.course_code',
                'course_catalogue.subjects.name',
                'class_scheduling.centres.name'
            )
            ->orderByDesc('total_income');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function queryCouponUsage(array $params): Collection
    {
        $query = DB::table('payment.coupon_redemptions')
            ->join('payment.coupon_codes', 'payment.coupon_redemptions.coupon_code_id', '=', 'payment.coupon_codes.id')
            ->join('payment.coupon_campaigns', 'payment.coupon_codes.campaign_id', '=', 'payment.coupon_campaigns.id')
            ->join('enrolment.enrolments', 'payment.coupon_redemptions.enrolment_id', '=', 'enrolment.enrolments.id')
            ->select(
                'payment.coupon_campaigns.name as campaign',
                'payment.coupon_campaigns.discount_type',
                DB::raw('COUNT(payment.coupon_redemptions.id) as redemption_count'),
                DB::raw('SUM(payment.coupon_redemptions.amount_discounted) as total_discount')
            )
            ->groupBy(
                'payment.coupon_campaigns.name',
                'payment.coupon_campaigns.discount_type'
            );

        if (!empty($params['date_from'])) {
            $query->where('payment.coupon_redemptions.redeemed_at', '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->where('payment.coupon_redemptions.redeemed_at', '<=', $params['date_to']);
        }

        return $query->get();
    }

    private function queryCertificatePrint(array $params): Collection
    {
        $query = DB::table('certificate.certificates')
            ->join('enrolment.enrolments', 'certificate.certificates.enrolment_id', '=', 'enrolment.enrolments.id')
            ->join('auth.learner_profiles', 'enrolment.enrolments.learner_id', '=', 'auth.learner_profiles.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->leftJoin('attendance.attendance_records', 'enrolment.enrolments.id', '=', 'attendance.attendance_records.enrolment_id')
            ->select(
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'course_catalogue.subjects.name as course',
                'class_scheduling.classes.class_code',
                'class_scheduling.classes.end_date as completion_date',
                DB::raw('CASE WHEN COUNT(attendance.attendance_records.id) > 0 THEN ROUND(COUNT(CASE WHEN attendance.attendance_records.status IN (\'present\', \'late\') THEN 1 END)::numeric / COUNT(attendance.attendance_records.id) * 100, 1) ELSE 0 END as attendance_rate')
            )
            ->groupBy(
                'auth.learner_profiles.id',
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.class_code',
                'class_scheduling.classes.end_date'
            )
            ->orderBy('auth.learner_profiles.name_en');

        if (!empty($params['class_id'])) {
            $query->where('enrolment.enrolments.class_id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryUnusedNumber(array $params): Collection
    {
        $query = DB::table('course_catalogue.courses')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->leftJoin('class_scheduling.classes', 'course_catalogue.courses.id', '=', 'class_scheduling.classes.course_id')
            ->select(
                'course_catalogue.courses.course_code',
                'course_catalogue.subjects.name as subject',
                'course_catalogue.courses.page_no'
            )
            ->whereNull('class_scheduling.classes.id')
            ->orderBy('course_catalogue.courses.course_code');

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function queryCourseManuscript(array $params): Collection
    {
        $query = DB::table('course_catalogue.course_text_versions')
            ->join('course_catalogue.subjects', 'course_catalogue.course_text_versions.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('course_catalogue.courses', 'course_catalogue.course_text_versions.subject_id', '=', 'course_catalogue.courses.subject_id')
            ->join('class_scheduling.classes', 'course_catalogue.courses.id', '=', 'class_scheduling.classes.course_id')
            ->select(
                'course_catalogue.subjects.subject_code',
                'course_catalogue.subjects.name as subject',
                'course_catalogue.course_text_versions.version_no',
                'course_catalogue.course_text_versions.content_html',
                'course_catalogue.course_text_versions.status'
            )
            ->where('course_catalogue.course_text_versions.status', 'published');

        if (!empty($params['class_id'])) {
            $query->where('class_scheduling.classes.id', $params['class_id']);
        }

        $query->orderBy('course_catalogue.subjects.subject_code');

        return $query->get();
    }

    private function queryInstructorData(array $params): Collection
    {
        $query = DB::table('auth.instructor_profiles')
            ->select(
                'auth.instructor_profiles.instructor_no',
                'auth.instructor_profiles.name',
                'auth.instructor_profiles.phone',
                'auth.instructor_profiles.email',
                'auth.instructor_profiles.status'
            )
            ->orderBy('auth.instructor_profiles.name');

        if (!empty($params['instructor_id'])) {
            $query->where('auth.instructor_profiles.id', $params['instructor_id']);
        }
        if (!empty($params['status'])) {
            $query->where('auth.instructor_profiles.status', $params['status']);
        }

        return $query->get();
    }

    private function querySubjectCourseData(array $params): Collection
    {
        $query = DB::table('course_catalogue.courses')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('course_catalogue.seasons', 'course_catalogue.courses.season_id', '=', 'course_catalogue.seasons.id')
            ->leftJoin('course_catalogue.subject_categories', 'course_catalogue.subjects.id', '=', 'course_catalogue.subject_categories.subject_id')
            ->leftJoin('course_catalogue.categories', 'course_catalogue.subject_categories.category_id', '=', 'course_catalogue.categories.id')
            ->select(
                'course_catalogue.subjects.subject_code',
                'course_catalogue.subjects.name as subject',
                'course_catalogue.courses.course_code',
                'course_catalogue.seasons.name as season',
                'course_catalogue.categories.name_en as category',
                'course_catalogue.subjects.tuition_fee',
                'course_catalogue.subjects.material_fee',
                'course_catalogue.subjects.total_hours'
            )
            ->orderBy('course_catalogue.seasons.name')
            ->orderBy('course_catalogue.subjects.subject_code');

        $query = $this->applySeasonFilter($query, $params);

        if (!empty($params['category_id'])) {
            $query->where('course_catalogue.subject_categories.category_id', $params['category_id']);
        }

        return $query->get();
    }

    private function queryClassroomTable(array $params): Collection
    {
        $query = DB::table('class_scheduling.classrooms')
            ->join('class_scheduling.centres', 'class_scheduling.classrooms.centre_id', '=', 'class_scheduling.centres.id')
            ->select(
                'class_scheduling.centres.name as centre',
                'class_scheduling.classrooms.code as classroom_code',
                'class_scheduling.classrooms.name as classroom_name',
                'class_scheduling.classrooms.capacity',
                'class_scheduling.classrooms.status'
            )
            ->orderBy('class_scheduling.centres.name')
            ->orderBy('class_scheduling.classrooms.code');

        if (!empty($params['centre_id'])) {
            $query->where('class_scheduling.classrooms.centre_id', $params['centre_id']);
        }

        return $query->get();
    }

    private function queryNoPageNumber(array $params): Collection
    {
        $query = DB::table('course_catalogue.courses')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'course_catalogue.courses.course_code',
                'course_catalogue.subjects.name as subject',
                'course_catalogue.courses.page_no'
            )
            ->whereNull('course_catalogue.courses.page_no')
            ->orderBy('course_catalogue.courses.course_code');

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function queryInstructorContract(array $params): Collection
    {
        $query = DB::table('instructor_finance.instructor_contracts')
            ->join('auth.instructor_profiles', 'instructor_finance.instructor_contracts.instructor_id', '=', 'auth.instructor_profiles.id')
            ->join('class_scheduling.classes', 'instructor_finance.instructor_contracts.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('class_scheduling.class_sessions', 'class_scheduling.classes.id', '=', 'class_scheduling.class_sessions.class_id')
            ->select(
                'auth.instructor_profiles.name as instructor_name',
                'course_catalogue.subjects.name as course',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'class_scheduling.classes.start_date',
                'class_scheduling.classes.end_date',
                'instructor_finance.instructor_contracts.fee_amount',
                'instructor_finance.instructor_contracts.fee_type',
                DB::raw('COUNT(DISTINCT class_scheduling.class_sessions.id) as total_sessions')
            )
            ->groupBy(
                'auth.instructor_profiles.name',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name',
                'class_scheduling.classes.start_date',
                'class_scheduling.classes.end_date',
                'instructor_finance.instructor_contracts.fee_amount',
                'instructor_finance.instructor_contracts.fee_type'
            )
            ->orderBy('auth.instructor_profiles.name');

        if (!empty($params['instructor_id'])) {
            $query->where('instructor_finance.instructor_contracts.instructor_id', $params['instructor_id']);
        }

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function queryAdvancedCourseNotice(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->select(
                'course_catalogue.subjects.name as course_name',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'class_scheduling.classes.start_date',
                'class_scheduling.classes.end_date',
                'course_catalogue.subjects.tuition_fee',
                DB::raw("CONCAT(class_scheduling.classes.schedule_day, ' ', class_scheduling.classes.start_time, '-', class_scheduling.classes.end_time) as schedule")
            );

        if (!empty($params['class_id'])) {
            $query->where('class_scheduling.classes.id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryAdvancedInstructorNotice(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->join('auth.instructor_profiles', 'class_scheduling.classes.instructor_id', '=', 'auth.instructor_profiles.id')
            ->leftJoin('enrolment.enrolments', function ($join) {
                $join->on('class_scheduling.classes.id', '=', 'enrolment.enrolments.class_id')
                    ->where('enrolment.enrolments.status', '=', 'confirmed');
            })
            ->select(
                'auth.instructor_profiles.name as instructor_name',
                'course_catalogue.subjects.name as course',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'class_scheduling.classes.start_date',
                'class_scheduling.classes.end_date',
                DB::raw("CONCAT(class_scheduling.classes.schedule_day, ' ', class_scheduling.classes.start_time, '-', class_scheduling.classes.end_time) as schedule"),
                DB::raw('COUNT(enrolment.enrolments.id) as student_count')
            )
            ->groupBy(
                'auth.instructor_profiles.name',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name',
                'class_scheduling.classes.start_date',
                'class_scheduling.classes.end_date',
                'class_scheduling.classes.schedule_day',
                'class_scheduling.classes.start_time',
                'class_scheduling.classes.end_time'
            )
            ->orderBy('auth.instructor_profiles.name');

        if (!empty($params['instructor_id'])) {
            $query->where('class_scheduling.classes.instructor_id', $params['instructor_id']);
        }
        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function queryCourseAnalysis(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('enrolment.enrolments', function ($join) {
                $join->on('class_scheduling.classes.id', '=', 'enrolment.enrolments.class_id')
                    ->where('enrolment.enrolments.status', '=', 'confirmed');
            })
            ->leftJoin('payment.receipts', 'enrolment.enrolments.id', '=', 'payment.receipts.enrolment_id')
            ->select(
                'class_scheduling.centres.name as centre',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.class_code',
                'class_scheduling.classes.capacity',
                DB::raw('COUNT(DISTINCT enrolment.enrolments.id) as enrolment_count'),
                DB::raw('COALESCE(SUM(payment.receipts.amount), 0) as total_revenue'),
                DB::raw('ROUND(COUNT(DISTINCT enrolment.enrolments.id)::numeric / NULLIF(class_scheduling.classes.capacity, 0) * 100, 1) as fill_rate')
            )
            ->groupBy(
                'class_scheduling.centres.name',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.class_code',
                'class_scheduling.classes.capacity',
                'class_scheduling.classes.id'
            )
            ->orderBy('class_scheduling.centres.name')
            ->orderBy('course_catalogue.subjects.name');

        $query = $this->applyCentreFilter($query, $params);
        $query = $this->applySeasonFilter($query, $params);

        return $query->get();
    }

    private function queryStudentPhoneList(array $params): Collection
    {
        $query = DB::table('enrolment.enrolments')
            ->join('auth.learner_profiles', 'enrolment.enrolments.learner_id', '=', 'auth.learner_profiles.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'auth.learner_profiles.phone',
                'auth.learner_profiles.email',
                'course_catalogue.subjects.name as course'
            )
            ->where('enrolment.enrolments.status', 'confirmed')
            ->orderBy('auth.learner_profiles.name_en');

        if (!empty($params['class_id'])) {
            $query->where('enrolment.enrolments.class_id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryInstructorCommunication(array $params): Collection
    {
        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->join('auth.instructor_profiles', 'class_scheduling.classes.instructor_id', '=', 'auth.instructor_profiles.id')
            ->leftJoin('class_scheduling.class_sessions', 'class_scheduling.classes.id', '=', 'class_scheduling.class_sessions.class_id')
            ->select(
                'auth.instructor_profiles.name as instructor_name',
                'auth.instructor_profiles.phone',
                'auth.instructor_profiles.email',
                'course_catalogue.subjects.name as course',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre',
                'class_scheduling.classes.start_date',
                DB::raw('COUNT(class_scheduling.class_sessions.id) as sessions')
            )
            ->groupBy(
                'auth.instructor_profiles.name',
                'auth.instructor_profiles.phone',
                'auth.instructor_profiles.email',
                'course_catalogue.subjects.name',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name',
                'class_scheduling.classes.start_date'
            )
            ->orderBy('class_scheduling.classes.start_date');

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }
        if (!empty($params['instructor_id'])) {
            $query->where('class_scheduling.classes.instructor_id', $params['instructor_id']);
        }

        return $query->get();
    }

    private function queryInstructorSignIn(array $params): Collection
    {
        $query = DB::table('class_scheduling.class_sessions')
            ->join('class_scheduling.classes', 'class_scheduling.class_sessions.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('auth.instructor_profiles', 'class_scheduling.class_sessions.instructor_id', '=', 'auth.instructor_profiles.id')
            ->select(
                'class_scheduling.class_sessions.date',
                'class_scheduling.class_sessions.start_time',
                'class_scheduling.class_sessions.end_time',
                'auth.instructor_profiles.name as instructor',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.class_code',
                'class_scheduling.centres.name as centre'
            )
            ->orderBy('class_scheduling.class_sessions.date')
            ->orderBy('class_scheduling.class_sessions.start_time');

        if (!empty($params['class_id'])) {
            $query->where('class_scheduling.class_sessions.class_id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryCertificateApplication(array $params): Collection
    {
        $query = DB::table('enrolment.enrolments')
            ->join('auth.learner_profiles', 'enrolment.enrolments.learner_id', '=', 'auth.learner_profiles.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->leftJoin('attendance.attendance_records', 'enrolment.enrolments.id', '=', 'attendance.attendance_records.enrolment_id')
            ->select(
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'auth.learner_profiles.id_no_encrypted',
                'course_catalogue.subjects.name as course',
                DB::raw('COUNT(CASE WHEN attendance.attendance_records.status IN (\'present\', \'late\') THEN 1 END) as attended'),
                DB::raw('COUNT(attendance.attendance_records.id) as total_sessions'),
                DB::raw('CASE WHEN COUNT(attendance.attendance_records.id) > 0 THEN ROUND(COUNT(CASE WHEN attendance.attendance_records.status IN (\'present\', \'late\') THEN 1 END)::numeric / COUNT(attendance.attendance_records.id) * 100, 1) ELSE 0 END as attendance_rate')
            )
            ->where('enrolment.enrolments.status', 'confirmed')
            ->groupBy(
                'auth.learner_profiles.id',
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'auth.learner_profiles.id_no_encrypted',
                'course_catalogue.subjects.name'
            )
            ->orderBy('auth.learner_profiles.name_en');

        if (!empty($params['class_id'])) {
            $query->where('enrolment.enrolments.class_id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryAttendanceSheet(array $params): Collection
    {
        $query = DB::table('enrolment.enrolments')
            ->join('auth.learner_profiles', 'enrolment.enrolments.learner_id', '=', 'auth.learner_profiles.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'auth.learner_profiles.phone',
                'course_catalogue.subjects.name as course',
                'class_scheduling.classes.class_code'
            )
            ->where('enrolment.enrolments.status', 'confirmed')
            ->orderBy('auth.learner_profiles.name_en');

        if (!empty($params['class_id'])) {
            $query->where('enrolment.enrolments.class_id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryNameLabels(array $params): Collection
    {
        $query = DB::table('enrolment.enrolments')
            ->join('auth.learner_profiles', 'enrolment.enrolments.learner_id', '=', 'auth.learner_profiles.id')
            ->join('class_scheduling.classes', 'enrolment.enrolments.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'auth.learner_profiles.name_en',
                'auth.learner_profiles.name_zh',
                'course_catalogue.subjects.name as course',
                'class_scheduling.classes.class_code'
            )
            ->where('enrolment.enrolments.status', 'confirmed')
            ->orderBy('auth.learner_profiles.name_en');

        if (!empty($params['class_id'])) {
            $query->where('enrolment.enrolments.class_id', $params['class_id']);
        }

        return $query->get();
    }

    private function queryInstructorPaymentSlip(array $params): Collection
    {
        $query = DB::table('instructor_finance.instructor_fee_items')
            ->join('auth.instructor_profiles', 'instructor_finance.instructor_fee_items.instructor_id', '=', 'auth.instructor_profiles.id')
            ->join('class_scheduling.classes', 'instructor_finance.instructor_fee_items.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'auth.instructor_profiles.name as instructor',
                'auth.instructor_profiles.instructor_no',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.class_code',
                'instructor_finance.instructor_fee_items.amount',
                'instructor_finance.instructor_fee_items.adjustment',
                'instructor_finance.instructor_fee_items.status'
            )
            ->orderBy('auth.instructor_profiles.name');

        if (!empty($params['instructor_id'])) {
            $query->where('instructor_finance.instructor_fee_items.instructor_id', $params['instructor_id']);
        }

        if (!empty($params['month'])) {
            $query->whereYear('instructor_finance.instructor_fee_items.calculated_at', substr($params['month'], 0, 4));
            $query->whereMonth('instructor_finance.instructor_fee_items.calculated_at', substr($params['month'], 5, 2));
        }

        return $query->get();
    }

    private function queryInstructorCheque(array $params): Collection
    {
        $query = DB::table('instructor_finance.cheque_records')
            ->join('instructor_finance.instructor_payment_batches', 'instructor_finance.cheque_records.payment_batch_id', '=', 'instructor_finance.instructor_payment_batches.id')
            ->join('auth.instructor_profiles', 'instructor_finance.instructor_payment_batches.instructor_id', '=', 'auth.instructor_profiles.id')
            ->select(
                'auth.instructor_profiles.name as instructor_name',
                'instructor_finance.cheque_records.cheque_no',
                'instructor_finance.cheque_records.amount',
                'instructor_finance.instructor_payment_batches.payment_date'
            )
            ->orderByDesc('instructor_finance.cheque_records.created_at');

        if (!empty($params['instructor_id'])) {
            $query->where('instructor_finance.instructor_payment_batches.instructor_id', $params['instructor_id']);
        }

        if (!empty($params['month'])) {
            $query->whereYear('instructor_finance.cheque_records.printed_at', substr($params['month'], 0, 4));
            $query->whereMonth('instructor_finance.cheque_records.printed_at', substr($params['month'], 5, 2));
        }

        $cheque = $query->first();

        if (!$cheque) {
            return collect();
        }

        $courses = DB::table('instructor_finance.instructor_fee_items')
            ->join('class_scheduling.classes', 'instructor_finance.instructor_fee_items.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->where('instructor_finance.instructor_fee_items.instructor_id', $params['instructor_id'] ?? 0)
            ->select(
                'course_catalogue.subjects.name as course_name',
                'class_scheduling.classes.class_code',
                'instructor_finance.instructor_fee_items.amount as fee'
            )
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();

        $month = $params['month'] ?? now()->format('Y-m');
        $period = date('M Y', strtotime($month . '-01'));

        $result = [
            'instructor_name' => $cheque->instructor_name,
            'cheque_no' => $cheque->cheque_no,
            'amount' => $cheque->amount,
            'period' => $period,
            'payment_date' => $cheque->payment_date,
            'courses' => $courses,
        ];

        return collect([$result]);
    }

    private function queryInstructorFeeSummary(array $params): Collection
    {
        $query = DB::table('instructor_finance.instructor_fee_items')
            ->join('auth.instructor_profiles', 'instructor_finance.instructor_fee_items.instructor_id', '=', 'auth.instructor_profiles.id')
            ->join('class_scheduling.classes', 'instructor_finance.instructor_fee_items.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'auth.instructor_profiles.name as instructor',
                'auth.instructor_profiles.instructor_no',
                DB::raw('COUNT(instructor_finance.instructor_fee_items.id) as class_count'),
                DB::raw('SUM(instructor_finance.instructor_fee_items.amount) as total_fees'),
                DB::raw('SUM(instructor_finance.instructor_fee_items.adjustment) as total_adjustments')
            )
            ->groupBy(
                'auth.instructor_profiles.id',
                'auth.instructor_profiles.name',
                'auth.instructor_profiles.instructor_no'
            )
            ->orderByDesc('total_fees');

        $query = $this->applySeasonFilter($query, $params);

        if (!empty($params['instructor_id'])) {
            $query->where('instructor_finance.instructor_fee_items.instructor_id', $params['instructor_id']);
        }

        return $query->get();
    }

    private function queryQuarterlyAnalysis(array $params): Collection
    {
        $year = $params['year'] ?? date('Y');
        $quarter = $params['quarter'] ?? ceil(date('n') / 3);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;
        $startDate = sprintf('%d-%02d-01', $year, $startMonth);
        $endDate = sprintf('%d-%02d-01', $year, $endMonth + 1);

        $query = DB::table('class_scheduling.classes')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
            ->leftJoin('enrolment.enrolments', function ($join) {
                $join->on('class_scheduling.classes.id', '=', 'enrolment.enrolments.class_id')
                    ->where('enrolment.enrolments.status', '=', 'confirmed');
            })
            ->leftJoin('payment.receipts', 'enrolment.enrolments.id', '=', 'payment.receipts.enrolment_id')
            ->select(
                'class_scheduling.centres.name as centre',
                'course_catalogue.subjects.name as subject',
                DB::raw('COUNT(DISTINCT class_scheduling.classes.id) as class_count'),
                DB::raw('COUNT(DISTINCT enrolment.enrolments.id) as enrolment_count'),
                DB::raw('COALESCE(SUM(payment.receipts.amount), 0) as total_revenue')
            )
            ->where('class_scheduling.classes.start_date', '>=', $startDate)
            ->where('class_scheduling.classes.start_date', '<', $endDate)
            ->groupBy('class_scheduling.centres.name', 'course_catalogue.subjects.name')
            ->orderBy('class_scheduling.centres.name')
            ->orderBy('course_catalogue.subjects.name');

        return $query->get();
    }

    private function queryAnnualTaxExport(array $params): Collection
    {
        $year = $params['year'] ?? date('Y');

        $query = DB::table('instructor_finance.instructor_fee_items')
            ->join('auth.instructor_profiles', 'instructor_finance.instructor_fee_items.instructor_id', '=', 'auth.instructor_profiles.id')
            ->join('class_scheduling.classes', 'instructor_finance.instructor_fee_items.class_id', '=', 'class_scheduling.classes.id')
            ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
            ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
            ->select(
                'auth.instructor_profiles.name as instructor',
                'auth.instructor_profiles.instructor_no',
                'auth.instructor_profiles.email',
                'course_catalogue.subjects.name as subject',
                'class_scheduling.classes.class_code',
                'instructor_finance.instructor_fee_items.amount',
                'instructor_finance.instructor_fee_items.adjustment',
                DB::raw('instructor_finance.instructor_fee_items.amount + instructor_finance.instructor_fee_items.adjustment as net_amount'),
                'instructor_finance.instructor_fee_items.calculated_at'
            )
            ->whereYear('instructor_finance.instructor_fee_items.calculated_at', $year)
            ->orderBy('auth.instructor_profiles.name')
            ->orderBy('instructor_finance.instructor_fee_items.calculated_at');

        if (!empty($params['instructor_id'])) {
            $query->where('instructor_finance.instructor_fee_items.instructor_id', $params['instructor_id']);
        }

        return $query->get();
    }

    private function generatePrintablePdf(string $reportType, array $params, Collection $data): string
    {
        $rows = $data->map(fn ($r) => (array) $r)->toArray();

        return match ($reportType) {
            'attendance_sheet' => $this->pdfService->generateAttendanceSheet(
                $this->buildAttendanceMeta($params),
                $rows,
                $this->buildSessionList($params)
            ),
            'name_labels' => $this->pdfService->generateNameLabels($rows),
            'instructor_sign_in' => $this->pdfService->generateSignInSheet(
                $this->buildAttendanceMeta($params),
                $this->buildSessionList($params)
            ),
            'certificate_application' => $this->pdfService->generateCertificateApplication(
                $this->buildAttendanceMeta($params),
                $rows
            ),
            'certificate_print' => $this->pdfService->generateCertificatePrint(
                $this->buildAttendanceMeta($params),
                $rows
            ),
            'instructor_contract' => $this->pdfService->generateInstructorContract(
                $this->buildAttendanceMeta($params),
                $rows[0] ?? []
            ),
            'advanced_course_notice' => $this->pdfService->generateAdvancedCourseNotice(
                $this->buildAttendanceMeta($params),
                $rows[0] ?? []
            ),
            'advanced_instructor_notice' => $this->pdfService->generateAdvancedInstructorNotice(
                $this->buildAttendanceMeta($params),
                $rows[0] ?? []
            ),
            'instructor_communication' => $this->pdfService->generateInstructorCommunication(
                $this->buildAttendanceMeta($params),
                $rows
            ),
            'instructor_cheque' => $this->pdfService->generateInstructorCheque(
                $this->buildAttendanceMeta($params),
                $rows[0] ?? []
            ),
            default => '',
        };
    }

    private function buildAttendanceMeta(array $params): array
    {
        if (!empty($params['class_id'])) {
            $class = DB::table('class_scheduling.classes')
                ->join('course_catalogue.courses', 'class_scheduling.classes.course_id', '=', 'course_catalogue.courses.id')
                ->join('course_catalogue.subjects', 'course_catalogue.courses.subject_id', '=', 'course_catalogue.subjects.id')
                ->join('class_scheduling.centres', 'class_scheduling.classes.centre_id', '=', 'class_scheduling.centres.id')
                ->leftJoin('auth.instructor_profiles', 'class_scheduling.classes.instructor_id', '=', 'auth.instructor_profiles.id')
                ->where('class_scheduling.classes.id', $params['class_id'])
                ->select(
                    'course_catalogue.subjects.name as course',
                    'class_scheduling.classes.class_code',
                    'class_scheduling.centres.name as centre',
                    'auth.instructor_profiles.name as instructor',
                    'class_scheduling.classes.start_date',
                    'class_scheduling.classes.end_date',
                    'class_scheduling.classes.capacity as student_count'
                )
                ->first();

            if ($class) {
                return [
                    'course' => $class->course ?? '',
                    'class_code' => $class->class_code ?? '',
                    'centre' => $class->centre ?? '',
                    'instructor' => $class->instructor ?? 'TBA',
                    'start_date' => $class->start_date ? date('d/m/Y', strtotime($class->start_date)) : '',
                    'end_date' => $class->end_date ? date('d/m/Y', strtotime($class->end_date)) : '',
                    'student_count' => $class->student_count ?? 0,
                ];
            }
        }

        return [
            'course' => '', 'class_code' => '', 'centre' => '',
            'instructor' => '', 'start_date' => '', 'end_date' => '', 'student_count' => 0,
        ];
    }

    private function buildSessionList(array $params): array
    {
        if (empty($params['class_id'])) {
            return [];
        }

        return DB::table('class_scheduling.class_sessions')
            ->where('class_id', $params['class_id'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                'date' => date('d/m', strtotime($s->date)),
                'time' => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5),
            ])
            ->toArray();
    }

    private function generateCsv(Collection $data): string
    {
        if ($data->isEmpty()) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, array_keys((array) $data->first()));

        foreach ($data as $row) {
            fputcsv($output, (array) $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
