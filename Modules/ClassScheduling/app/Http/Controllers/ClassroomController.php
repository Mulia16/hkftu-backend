<?php

namespace Modules\ClassScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\ClassScheduling\Models\Centre;
use Modules\ClassScheduling\Models\Classroom;

class ClassroomController extends Controller
{
    public function index(Centre $centre): JsonResponse
    {
        $classrooms = $centre->classrooms()->orderBy('code')->get();

        return response()->json(['data' => $classrooms]);
    }

    public function show(Centre $centre, Classroom $classroom): JsonResponse
    {
        return response()->json(['data' => $classroom]);
    }

    public function store(Request $request, Centre $centre): JsonResponse
    {
        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('class_scheduling.classrooms')->where('centre_id', $centre->id),
            ],
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'facilities_json' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        $classroom = $centre->classrooms()->create($validated);

        return response()->json(['data' => $classroom], 201);
    }

    public function update(Request $request, Centre $centre, Classroom $classroom): JsonResponse
    {
        $validated = $request->validate([
            'code' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('class_scheduling.classrooms')
                    ->where('centre_id', $centre->id)
                    ->ignore($classroom->id),
            ],
            'name' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'facilities_json' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        $classroom->update($validated);

        return response()->json(['data' => $classroom]);
    }

    public function destroy(Centre $centre, Classroom $classroom): JsonResponse
    {
        $classroom->delete();

        return response()->json(['data' => ['message' => 'Classroom deleted.']]);
    }
}
