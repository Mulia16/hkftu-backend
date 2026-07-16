<?php

namespace Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reporting\DTOs\RunReportData;
use Modules\Reporting\Models\ReportRun;
use Modules\Reporting\Services\ReportService;

class ReportRunController extends Controller
{
    public function store(RunReportData $data, ReportService $service): JsonResponse
    {
        $run = $service->run($data->template_id, request()->user()->id, $data->parameters);

        return response()->json(['data' => $run->load('template')], 201);
    }

    public function index(): JsonResponse
    {
        $runs = ReportRun::with('template')->orderByDesc('id')->paginate(20);

        return response()->json($runs);
    }

    public function show(int $id): JsonResponse
    {
        $run = ReportRun::with('template')->findOrFail($id);

        return response()->json(['data' => $run]);
    }
}
