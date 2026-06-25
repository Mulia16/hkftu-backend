<?php

namespace Modules\ClassScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ClassScheduling\DTOs\StoreCentreData;
use Modules\ClassScheduling\Models\Centre;

class CentreController extends Controller
{
    public function index(): JsonResponse
    {
        $centres = Centre::orderBy('code')->get();

        return response()->json(['data' => $centres]);
    }

    public function show(Centre $centre): JsonResponse
    {
        $centre->load('classrooms');

        return response()->json(['data' => $centre]);
    }

    public function store(StoreCentreData $data): JsonResponse
    {
        $centre = Centre::create($data->toArray());

        return response()->json(['data' => $centre], 201);
    }

    public function update(Request $request, Centre $centre): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:class_scheduling.centres,code,'.$centre->id,
            'name' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:500',
            'phone' => 'nullable|string|max:20',
            'opening_hours' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        $centre->update($validated);

        return response()->json(['data' => $centre]);
    }

    public function destroy(Centre $centre): JsonResponse
    {
        $centre->delete();

        return response()->json(['data' => ['message' => 'Centre deleted.']]);
    }
}
