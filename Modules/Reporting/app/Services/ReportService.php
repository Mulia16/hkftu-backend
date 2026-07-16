<?php

namespace Modules\Reporting\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Reporting\Exports\ReportExport;
use Modules\Reporting\Models\ReportRun;
use Modules\Reporting\Models\ReportTemplate;

class ReportService
{
    public function run(int $templateId, int $userId, ?array $parameters = null): ReportRun
    {
        $run = ReportRun::create([
            'template_id' => $templateId,
            'requested_by' => $userId,
            'parameters_json' => $parameters,
            'status' => 'pending',
        ]);

        $this->executeReport($run);

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
                default => collect(),
            };

            $isXlsx = $template->format === 'xlsx';
            $ext = $isXlsx ? 'xlsx' : 'csv';
            $filename = $template->query_key . '_' . $run->id . '_' . now()->format('YmdHis') . '.' . $ext;
            $directory = 'reports';
            $path = $directory . '/' . $filename;

            if ($isXlsx) {
                $fullPath = Storage::disk('public')->path($path);
                Excel::store(new ReportExport($data), $path, 'public');
            } else {
                Storage::disk('public')->put($path, $this->generateCsv($data));
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

    private function queryReceiptTotal(array $params): \Illuminate\Support\Collection
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

        if (!empty($params['centre_id'])) {
            $query->where('class_scheduling.classes.centre_id', $params['centre_id']);
        }

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        if (!empty($params['date_from'])) {
            $query->where('payment.receipts.issued_at', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->where('payment.receipts.issued_at', '<=', $params['date_to']);
        }

        return $query->get();
    }

    private function queryFullClass(array $params): \Illuminate\Support\Collection
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
                'class_scheduling.classes.id as class_id',
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

        if (!empty($params['centre_id'])) {
            $query->where('class_scheduling.classes.centre_id', $params['centre_id']);
        }

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function queryDangerClass(array $params): \Illuminate\Support\Collection
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
                'class_scheduling.classes.id as class_id',
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

        if (!empty($params['centre_id'])) {
            $query->where('class_scheduling.classes.centre_id', $params['centre_id']);
        }

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function querySubjectData(array $params): \Illuminate\Support\Collection
    {
        $query = DB::table('course_catalogue.subjects')
            ->select(
                'course_catalogue.subjects.subject_code',
                'course_catalogue.subjects.name',
                'course_catalogue.subjects.tuition_fee',
                'course_catalogue.subjects.material_fee',
                'course_catalogue.subjects.instructor_fee_default',
                'course_catalogue.subjects.total_hours',
                'course_catalogue.subjects.status'
            );

        if (!empty($params['status'])) {
            $query->where('course_catalogue.subjects.status', $params['status']);
        }

        return $query->get();
    }

    private function queryQuotaTable(array $params): \Illuminate\Support\Collection
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

        if (!empty($params['centre_id'])) {
            $query->where('class_scheduling.classes.centre_id', $params['centre_id']);
        }

        if (!empty($params['season_id'])) {
            $query->where('course_catalogue.courses.season_id', $params['season_id']);
        }

        return $query->get();
    }

    private function generateCsv(\Illuminate\Support\Collection $data): string
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
