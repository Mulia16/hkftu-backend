<?php

namespace Modules\InstructorFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\InstructorFinance\Models\ChequeRecord;

class ChequeRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ChequeRecord::with(['paymentBatch', 'instructor']);

        if ($batchId = $request->input('payment_batch_id')) {
            $query->where('payment_batch_id', $batchId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $cheques = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 25));

        return response()->json($cheques);
    }

    public function markPrinted(int $id): JsonResponse
    {
        $cheque = ChequeRecord::findOrFail($id);
        $cheque->update([
            'status' => 'printed',
            'printed_at' => now(),
        ]);

        return response()->json(['data' => $cheque->load(['paymentBatch', 'instructor'])]);
    }
}
