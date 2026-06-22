<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->when($request->action, fn ($q) => $q->where('action', 'ilike', "%{$request->action}%"))
            ->when($request->resource_type, fn ($q) => $q->where('resource_type', $request->resource_type))
            ->orderByDesc('created_at');

        return response()->json(['data' => $query->paginate($request->integer('per_page', 25))]);
    }
}
