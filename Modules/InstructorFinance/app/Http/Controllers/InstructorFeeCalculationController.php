<?php

namespace Modules\InstructorFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\InstructorFinance\DTOs\CalculateFeeData;
use Modules\InstructorFinance\Models\InstructorFeeItem;
use Modules\InstructorFinance\Services\InstructorFeeCalculationService;

class InstructorFeeCalculationController extends Controller
{
    public function __construct(
        private InstructorFeeCalculationService $feeService,
    ) {}

    public function calculate(Request $request): JsonResponse
    {
        $data = CalculateFeeData::from($request->all());

        $items = $this->feeService->calculateForSeason(
            $data->season_id,
            $data->centre_id,
            $data->instructor_id,
        );

        return response()->json(['data' => $items->each(fn ($item) => $item->load(['class.course.subject', 'instructor', 'feeRule']))]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = InstructorFeeItem::with(['class.course.subject', 'instructor', 'feeRule']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($instructorId = $request->input('instructor_id')) {
            $query->where('instructor_id', $instructorId);
        }

        $items = $query->orderByDesc('calculated_at')->paginate($request->integer('per_page', 25));

        return response()->json($items);
    }

    public function updateAdjustment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'adjustment' => ['required', 'numeric'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $item = InstructorFeeItem::findOrFail($id);
        $item->update([
            'adjustment' => $request->input('adjustment'),
        ]);

        return response()->json(['data' => $item->load(['class.course.subject', 'instructor'])]);
    }
}
