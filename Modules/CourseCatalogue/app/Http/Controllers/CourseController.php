<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CourseCatalogue\Models\Course;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Course::with(['season', 'subject'])
            ->when($request->season_id, fn ($q) => $q->where('season_id', $request->season_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('course_code', 'ilike', "%{$request->search}%"))
            ->orderBy('course_code');

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'season_id'   => 'required|exists:course_catalogue.seasons,id',
            'subject_id'  => 'required|exists:course_catalogue.subjects,id',
            'course_code' => 'required|string|max:30|unique:course_catalogue.courses,course_code',
            'page_no'     => 'nullable|integer|min:1',
            'status'      => 'sometimes|in:draft,review,approved,published,archived',
            'publish_at'  => 'nullable|date',
        ]);

        $course = Course::create($data);

        return response()->json(['data' => $course->load(['season', 'subject'])], 201);
    }

    public function show(int $id): JsonResponse
    {
        $course = Course::with(['season', 'subject'])->findOrFail($id);

        return response()->json(['data' => $course]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'course_code' => "sometimes|string|max:30|unique:course_catalogue.courses,course_code,{$id}",
            'page_no'     => 'nullable|integer|min:1',
            'status'      => 'sometimes|in:draft,review,approved,published,archived',
            'publish_at'  => 'nullable|date',
        ]);

        $course->update($data);

        return response()->json(['data' => $course->load(['season', 'subject'])]);
    }

    public function destroy(int $id): JsonResponse
    {
        Course::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
