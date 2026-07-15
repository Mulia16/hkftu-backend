<?php

namespace Modules\InstructorFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\InstructorFinance\DTOs\StoreInstructorFeeRuleData;
use Modules\InstructorFinance\Models\InstructorFeeRule;

class InstructorFeeRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InstructorFeeRule::with(['subject', 'course']);

        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        $rules = $query->orderByDesc('effective_from')->paginate($request->integer('per_page', 25));

        return response()->json($rules);
    }

    public function store(Request $request): JsonResponse
    {
        $data = StoreInstructorFeeRuleData::from($request->all());

        $rule = InstructorFeeRule::create($data->toArray());

        return response()->json(['data' => $rule->load(['subject', 'course'])], 201);
    }

    public function show(int $id): JsonResponse
    {
        $rule = InstructorFeeRule::with(['subject', 'course'])->findOrFail($id);

        return response()->json(['data' => $rule]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rule = InstructorFeeRule::findOrFail($id);

        $data = StoreInstructorFeeRuleData::from($request->all());

        $rule->update($data->toArray());

        return response()->json(['data' => $rule->load(['subject', 'course'])]);
    }

    public function destroy(int $id): JsonResponse
    {
        InstructorFeeRule::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
