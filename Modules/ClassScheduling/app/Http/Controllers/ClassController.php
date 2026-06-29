<?php

namespace Modules\ClassScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\ClassScheduling\DTOs\StoreClassData;
use Modules\ClassScheduling\Models\ClashCheckResult;
use Modules\ClassScheduling\Models\Classroom;
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
        private AuditLogger $auditLogger,
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

    public function store(StoreClassData $data): JsonResponse
    {
        $data = $data->toArray();

        if (isset($data['classroom_id'])) {
            $classroom = Classroom::find($data['classroom_id']);
            if ($classroom && $classroom->centre_id != $data['centre_id']) {
                return ApiError::respond('VALIDATION_ERROR', 'Classroom does not belong to the selected centre.', 422, [
                    'fieldErrors' => ['classroom_id' => ['Classroom does not belong to the selected centre.']],
                ]);
            }
        }

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

        $this->auditLogger->record('class.create', 'class', $class->id, after: $class->toArray());

        return response()->json(['data' => $class->load(['course.subject', 'centre', 'classroom', 'sessions'])], 201);
    }

    public function show(int $id): JsonResponse
    {
        $class = CourseClass::with(['course.subject', 'course.season', 'centre', 'classroom', 'schedulePattern', 'sessions', 'clashResults'])->findOrFail($id);

        return response()->json(['data' => $class]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $class = CourseClass::findOrFail($id);

        $data = $request->validate([
            'class_code' => "sometimes|string|max:30|unique:class_scheduling.classes,class_code,{$id}",
            'centre_id' => 'sometimes|exists:class_scheduling.centres,id',
            'classroom_id' => 'nullable|exists:class_scheduling.classrooms,id',
            'capacity' => 'sometimes|integer|min:1',
            'min_students' => 'sometimes|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'instructor_id' => 'nullable|exists:auth.users,id',
        ]);

        $centreId = $data['centre_id'] ?? $class->centre_id;
        $classroomId = $data['classroom_id'] ?? $class->classroom_id;

        if ($classroomId) {
            $classroom = Classroom::find($classroomId);
            if ($classroom && $classroom->centre_id != $centreId) {
                return ApiError::respond('VALIDATION_ERROR', 'Classroom does not belong to the selected centre.', 422, [
                    'fieldErrors' => ['classroom_id' => ['Classroom does not belong to the selected centre.']],
                ]);
            }
        }

        $before = $class->toArray();

        $class->update($data);
        $this->clashCheck->run($class);

        $this->auditLogger->record('class.update', 'class', $class->id, before: $before, after: $class->toArray());

        return response()->json(['data' => $class->load(['course.subject', 'centre', 'classroom'])]);
    }

    public function destroy(int $id): JsonResponse
    {
        $class = CourseClass::findOrFail($id);
        $class->delete();

        $this->auditLogger->record('class.delete', 'class', $id);

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

        $this->auditLogger->record('class.publish', 'class', $class->id);

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
                'status' => $class->status,
            ],
        ]);
    }

    public function resolveClash(Request $request, int $classId, int $clashId): JsonResponse
    {
        $request->validate(['resolution_note' => 'nullable|string|max:500']);

        $clash = ClashCheckResult::where('class_id', $classId)->findOrFail($clashId);

        $clash->update([
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return response()->json(['data' => $clash]);
    }
}
