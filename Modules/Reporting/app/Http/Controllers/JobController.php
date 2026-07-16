<?php

namespace Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Reporting\Models\ExportJob;
use Modules\Reporting\Models\ReportRun;

class JobController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $run = ReportRun::with('template')->find($id);

        if ($run) {
            return response()->json(['data' => [
                'id' => $run->id,
                'type' => 'report_run',
                'status' => $run->status,
                'file_path' => $run->file_path,
                'error_message' => $run->error_message,
                'started_at' => $run->started_at,
                'finished_at' => $run->finished_at,
                'template' => $run->template,
            ]]);
        }

        $job = ExportJob::find($id);

        if ($job) {
            return response()->json(['data' => [
                'id' => $job->id,
                'type' => 'export_job',
                'status' => $job->status,
                'export_type' => $job->export_type,
                'file_path' => $job->file_path,
                'error_message' => $job->error_message,
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
            ]]);
        }

        return response()->json(['message' => 'Job not found'], 404);
    }

    public function download(int $id): JsonResponse
    {
        $run = ReportRun::find($id);

        if ($run) {
            if ($run->status !== 'completed' || !$run->file_path) {
                return response()->json(['message' => 'File not ready'], 400);
            }

            if (!Storage::disk('public')->exists($run->file_path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            return response()->json(['data' => [
                'download_url' => '/storage/' . $run->file_path,
                'filename' => basename($run->file_path),
            ]]);
        }

        $job = ExportJob::find($id);

        if ($job) {
            if ($job->status !== 'completed' || !$job->file_path) {
                return response()->json(['message' => 'File not ready'], 400);
            }

            if (!Storage::disk('public')->exists($job->file_path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            return response()->json(['data' => [
                'download_url' => '/storage/' . $job->file_path,
                'filename' => basename($job->file_path),
            ]]);
        }

        return response()->json(['message' => 'Job not found'], 404);
    }
}
