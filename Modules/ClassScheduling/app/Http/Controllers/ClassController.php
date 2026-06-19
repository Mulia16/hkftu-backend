<?php

namespace Modules\ClassScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\ClassScheduling\Models\SchedulePattern;
use Modules\ClassScheduling\Services\ClashCheckService;
use Modules\ClassScheduling\Services\SessionGeneratorService;
use Modules\CourseCatalogue\Models\Course;

class ClassController extends Controller
{
    public function __construct(
        private ClashCheckService $clashCheck,
        private SessionGeneratorService $sessionGenerator,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = CourseClass::with(['course.subject', 'course.season', 'centre', 'classroom'])
            ->when($request->season_id, fn ($q) => $q->whereHas('course', fn ($q) => $q->where('season_id', $request->season_id)))
            ->when($request->centre_id, fn ($q) => $q->where('centre_id', $request->centre_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('class_code', 'ilike', "%{$request->search}%"))
            ->orderBy('class_code');

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'season_id'    => 'required|exists:course_catalogue.seasons,id',
            'subject_id'   => 'required|exists:course_catalogue.subjects,id',
            'class_code'   => 'required|string|max:30|unique:class_scheduling.classes,class_code',
            'centre_id'    => 'required|exists:class_scheduling.centres,id',
            'classroom_id' => 'nullable|exists:class_scheduling.classrooms,id',
            'capacity'     => 'required|integer|min:1',
            'min_students' => 'sometimes|integer|min:1',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'instructor_id' => 'nullable|exists:auth.users,id',
            'schedule_pattern' => 'nullable|array',
            'schedule_pattern.type' => 'required_with:schedule_pattern|in:weekly,one_off',
            'schedule_pattern.days_of_week' => 'nullable|array',
            'schedule_pattern.days_of_week.*' => 'integer|between:0,6',
            'schedule_pattern.start_time' => 'required_with:schedule_pattern|date_format:H:i',
            'schedule_pattern.end_time' => 'required_with:schedule_pattern|date_format:H:i|after:schedule_pattern.start_time',
            'schedule_pattern.overrides' => 'nullable|array',
        ]);

        $course = Course::firstOrCreate(
            ['season_id' => $data['season_id'], 'subject_id' => $data['subject_id']],
            ['course_code' => 'C-'.$data['season_id'].'-'.$data['subject_id'], 'status' => 'draft'],
        );

        $patternData = $data['schedule_pattern'] ?? null;
        unset($data['season_id'], $data['subject_id'], $data['schedule_pattern']);
        $data['course_id'] = $course->id;

        $pattern = null;
        if ($patternData) {
            $pattern = SchedulePattern::create($patternData);
            $data['schedule_pattern_id'] = $pattern->id;
        }

        $class = CourseClass::create($data);

        if ($pattern) {
            $this->sessionGenerator->generate($class);
            $this->clashCheck->run($class);
        }

        return response()->json(['data' => $class->load(['course.subject', 'centre', 'classroom', 'sessions'])], 201);
    }

    public function show(int $id): JsonResponse
    {
        $class = CourseClass::with(['course.subject', 'course.season', 'centre', 'classroom', 'sessions', 'clashResults'])->findOrFail($id);

        return response()->json(['data' => $class]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $class = CourseClass::findOrFail($id);

        $data = $request->validate([
            'class_code'   => "sometimes|string|max:30|unique:class_scheduling.classes,class_code,{$id}",
            'centre_id'    => 'sometimes|exists:class_scheduling.centres,id',
            'classroom_id' => 'nullable|exists:class_scheduling.classrooms,id',
            'capacity'     => 'sometimes|integer|min:1',
            'min_students' => 'sometimes|integer|min:1',
            'start_date'   => 'sometimes|date',
            'end_date'     => 'sometimes|date|after_or_equal:start_date',
            'instructor_id' => 'nullable|exists:auth.users,id',
        ]);

        $class->update($data);
        $this->clashCheck->run($class);

        return response()->json(['data' => $class->load(['course.subject', 'centre', 'classroom'])]);
    }

    public function destroy(int $id): JsonResponse
    {
        CourseClass::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    public function publish(int $id): JsonResponse
    {
        $class = CourseClass::with('clashResults')->findOrFail($id);

        $errors = $class->clashResults->where('severity', 'error')->values();

        if ($errors->isNotEmpty()) {
            return ApiError::respond('CLASH_ERROR', 'Class has unresolved clash errors and cannot be published.', 422, [
                'clashes' => $errors,
            ]);
        }

        $class->update(['status' => 'published']);

        return response()->json(['data' => $class]);
    }

    public function sessions(int $id): JsonResponse
    {
        $class = CourseClass::findOrFail($id);

        $sessions = $class->sessions()->orderBy('session_no')->get();

        return response()->json(['data' => $sessions]);
    }

    public function clashCheck(int $id): JsonResponse
    {
        $class = CourseClass::findOrFail($id);

        $results = $this->clashCheck->run($class);

        return response()->json(['data' => $results]);
    }

    public function availability(int $id): JsonResponse
    {
        $class = CourseClass::findOrFail($id);

        return response()->json([
            'data' => [
                'class_id' => $class->id,
                'capacity' => $class->capacity,
                'status'   => $class->status,
            ],
        ]);
    }
}
