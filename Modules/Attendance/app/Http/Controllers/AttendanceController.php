<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Attendance\DTOs\BatchAttendanceData;
use Modules\Attendance\DTOs\UpdateAttendanceData;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Services\AttendanceService;
use Modules\Auth\Services\AuditLogger;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\ClassScheduling\Models\CourseClass;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AuditLogger $auditLogger,
    ) {}

    public function grid(int $classId): JsonResponse
    {
        $class = CourseClass::findOrFail($classId);
        $data = $this->attendanceService->getGrid($class);

        return response()->json(['data' => $data]);
    }

    public function save(BatchAttendanceData $data, Request $request): JsonResponse
    {
        $session = ClassSession::findOrFail($data->class_session_id);
        $user = $request->user();

        $results = $this->attendanceService->batchUpsert(
            $data->class_session_id,
            $data->records,
            $user->id,
        );

        $this->auditLogger->record('attendance.save', 'attendance', $session->id, after: [
            'session_id' => $session->id,
            'records_count' => $results->count(),
        ]);

        return response()->json(['data' => $results]);
    }

    public function submit(int $sessionId, Request $request): JsonResponse
    {
        $session = ClassSession::findOrFail($sessionId);
        $user = $request->user();

        $result = $this->attendanceService->submit($session, $user->id);

        $this->auditLogger->record('attendance.submit', 'attendance', $session->id, after: $result);

        return response()->json(['data' => $result]);
    }

    public function show(string $id): JsonResponse
    {
        $record = AttendanceRecord::with(['classSession', 'enrolment.learner', 'marker'])->findOrFail((int) $id);

        return response()->json(['data' => $record]);
    }

    public function update(string $id, UpdateAttendanceData $data): JsonResponse
    {
        $record = AttendanceRecord::findOrFail((int) $id);
        $before = $record->toArray();

        $record->update([
            'status' => $data->status,
            'remarks' => $data->remarks,
            'marked_by' => request()->user()->id,
            'marked_at' => now(),
        ]);

        $this->auditLogger->record('attendance.update', 'attendance', $id, before: $before, after: $record->toArray());

        return response()->json(['data' => $record]);
    }

    public function learnerHistory(Request $request): JsonResponse
    {
        $learnerId = $request->integer('learner_id');
        if (! $learnerId) {
            return ApiError::respond('MISSING_LEARNER_ID', 'learner_id is required.', 422);
        }

        $history = $this->attendanceService->getLearnerHistory($learnerId);

        return response()->json(['data' => $history]);
    }
}
