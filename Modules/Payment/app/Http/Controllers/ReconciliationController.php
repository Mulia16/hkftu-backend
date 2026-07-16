<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Payment\DTOs\StoreReconciliationData;
use Modules\Payment\Models\ReconciliationBatch;
use Modules\Payment\Services\ReconciliationService;

class ReconciliationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $batches = ReconciliationBatch::withCount('items')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($batches);
    }

    public function store(StoreReconciliationData $data, ReconciliationService $service, Request $request): JsonResponse
    {
        $batch = $service->createBatch($data, $request->user()->id);

        return response()->json(['data' => $batch], 201);
    }

    public function show(int $id): JsonResponse
    {
        $batch = ReconciliationBatch::with(['items', 'creator'])->findOrFail($id);

        return response()->json(['data' => $batch]);
    }

    public function match(int $id, ReconciliationService $service): JsonResponse
    {
        $batch = $service->autoMatch($id);

        return response()->json(['data' => $batch]);
    }

    public function close(int $id, ReconciliationService $service): JsonResponse
    {
        $batch = $service->closeBatch($id);

        return response()->json(['data' => $batch]);
    }

    public function exceptions(int $id, ReconciliationService $service): JsonResponse
    {
        $exceptions = $service->getExceptions($id);

        return response()->json(['data' => $exceptions]);
    }
}
