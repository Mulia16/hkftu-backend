<?php

namespace Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Reporting\Models\ReportTemplate;

class ReportTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = ReportTemplate::orderBy('group')->orderBy('name')->get();

        return response()->json(['data' => $templates]);
    }
}
