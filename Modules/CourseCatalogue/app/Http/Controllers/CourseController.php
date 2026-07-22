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

    public function search(Request $request): JsonResponse
    {
        $query = Course::with(['season', 'subject.categories', 'classes.centre', 'classes.classroom'])
            ->where('status', 'published')
            ->when($request->season_id, fn ($q) => $q->where('season_id', $request->season_id))
            ->when($request->category_id, fn ($q) => $q->whereHas('subject.categories', fn ($q) => $q->where('id', $request->category_id)->orWhere('parent_id', $request->category_id)))
            ->when($request->keyword, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('course_code', 'ilike', "%{$request->keyword}%")
                    ->orWhereHas('subject', fn ($q) => $q->where('name', 'ilike', "%{$request->keyword}%"));
            }))
            ->when($request->centre_id, fn ($q) => $q->whereHas('classes', fn ($q) => $q->where('centre_id', $request->centre_id)->where('status', 'published')))
            ->orderBy('course_code');

        return response()->json($query->paginate($request->integer('per_page', 12)));
    }

    public function detail(string $courseCode): JsonResponse
    {
        $course = Course::with([
            'season',
            'subject.categories',
            'subject' => fn ($q) => $q->withTrashed(),
            'classes.centre',
            'classes.classroom',
            'classes.schedulePattern',
            'classes.sessions',
        ])
            ->where('course_code', $courseCode)
            ->firstOrFail();

        $seatService = app(\Modules\Enrolment\Services\SeatReservationService::class);
        $course->classes->each(function ($class) use ($seatService) {
            $class->setAttribute('available_seats', $seatService->calculateAvailableSeats($class));
        });

        return response()->json(['data' => $course]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'season_id' => 'required|exists:course_catalogue.seasons,id',
            'subject_id' => 'required|exists:course_catalogue.subjects,id',
            'course_code' => 'required|string|max:30|unique:course_catalogue.courses,course_code',
            'page_no' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:draft,review,approved,published,archived',
            'publish_at' => 'nullable|date',
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
            'page_no' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:draft,review,approved,published,archived',
            'publish_at' => 'nullable|date',
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
