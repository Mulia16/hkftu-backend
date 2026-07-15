<?php

namespace Modules\InstructorFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\InstructorFinance\DTOs\StorePaymentBatchData;
use Modules\InstructorFinance\Models\ChequeRecord;
use Modules\InstructorFinance\Models\InstructorFeeItem;
use Modules\InstructorFinance\Models\InstructorPaymentBatch;

class InstructorPaymentBatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InstructorPaymentBatch::with(['season', 'centre', 'approver', 'cheques']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $batches = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 25));

        return response()->json($batches);
    }

    public function store(Request $request): JsonResponse
    {
        $data = StorePaymentBatchData::from($request->all());

        $items = InstructorFeeItem::whereIn('id', $data->fee_item_ids)
            ->where('status', 'calculated')
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['error' => ['code' => 'NO_ITEMS', 'message' => 'No calculcated fee items found.']], 422);
        }

        $totalAmount = $items->sum(fn ($item) => (float) $item->amount + (float) $item->adjustment);

        $batch = InstructorPaymentBatch::create([
            'season_id' => $data->season_id,
            'centre_id' => $data->centre_id,
            'total_amount' => $totalAmount,
            'status' => 'draft',
        ]);

        $items->each(fn ($item) => $item->update(['status' => 'approved']));

        $grouped = $items->groupBy('instructor_id');
        foreach ($grouped as $instructorId => $instructorItems) {
            $user = User::find($instructorId);
            $amount = $instructorItems->sum(fn ($item) => (float) $item->amount + (float) $item->adjustment);

            ChequeRecord::create([
                'payment_batch_id' => $batch->id,
                'instructor_id' => $instructorId,
                'payee' => $user?->name ?? 'Unknown',
                'amount' => $amount,
                'status' => 'draft',
            ]);
        }

        return response()->json(['data' => $batch->load(['season', 'centre', 'cheques.instructor'])], 201);
    }

    public function show(int $id): JsonResponse
    {
        $batch = InstructorPaymentBatch::with(['season', 'centre', 'approver', 'cheques.instructor'])
            ->findOrFail($id);

        return response()->json(['data' => $batch]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $batch = InstructorPaymentBatch::findOrFail($id);

        if ($batch->status !== 'draft') {
            return response()->json(['error' => ['code' => 'INVALID_STATUS', 'message' => 'Only draft batches can be approved.']], 422);
        }

        $batch->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $batch->load(['season', 'centre', 'cheques.instructor'])]);
    }
}
