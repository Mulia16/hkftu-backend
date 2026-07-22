<?php

namespace Modules\InstructorFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\InstructorProfile;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuditLogger;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;
use Modules\InstructorFinance\Models\InstructorContract;
use Modules\Notification\Services\NotificationService;

class InstructorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InstructorProfile::with('user')
            ->whereHas('user.roles', fn ($q) => $q->where('name', 'instructor'));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $instructors = $query->paginate($request->integer('per_page', 25));

        return response()->json($instructors);
    }

    public function show(int $id): JsonResponse
    {
        $instructor = InstructorProfile::with('user')
            ->whereHas('user.roles', fn ($q) => $q->where('name', 'instructor'))
            ->findOrFail($id);

        $contracts = InstructorContract::where('instructor_id', $instructor->user_id)
            ->with(['class.course.subject', 'class.centre'])
            ->get();

        return response()->json([
            'data' => $instructor,
            'contracts' => $contracts,
        ]);
    }

    public function teachingSummary(int $id, Request $request): JsonResponse
    {
        $instructor = InstructorProfile::findOrFail($id);

        return response()->json(['data' => $this->buildTeachingSummary($instructor->user_id, $request->input('season_id'))]);
    }

    public function teachingSummaryMe(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->buildTeachingSummary($request->user()->id, $request->input('season_id'))]);
    }

    public function cancelSession(int $sessionId, Request $request, NotificationService $notifications, AuditLogger $auditLogger): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $session = ClassSession::with('courseClass.course.subject')->findOrFail($sessionId);
        $class = $session->courseClass;

        if (! $class || $class->instructor_id !== $request->user()->id) {
            return ApiError::respond('FORBIDDEN', 'You can only cancel sessions for classes you teach.', 403);
        }

        if ($session->status === 'cancelled') {
            return ApiError::respond('INVALID_STATUS', 'Session is already cancelled.', 422);
        }

        $session->update(['status' => 'cancelled']);

        $subjectName = $class->course?->subject?->name ?? 'your class';
        $dateLabel = $session->date?->toDateString() ?? '';
        $reason = $validated['reason'] ?? null;

        $enrolments = Enrolment::where('class_id', $class->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->with('learner')
            ->get();

        foreach ($enrolments as $enrolment) {
            $recipient = $enrolment->learner?->email ?? $enrolment->learner?->phone;

            if (! $recipient) {
                continue;
            }

            $notifications->send(
                channel: 'email',
                recipient: $recipient,
                subject: 'Class Session Cancelled',
                body: "The session of {$subjectName} on {$dateLabel} has been cancelled. A make-up session will be arranged."
                    .($reason ? " Reason: {$reason}" : ''),
                relatedType: 'class_session',
                relatedId: $session->id,
            );
        }

        $auditLogger->record('class_session.cancel', 'class_session', $session->id, after: [
            'status' => 'cancelled',
            'reason' => $reason,
            'notified_students' => $enrolments->count(),
        ]);

        return response()->json(['data' => [
            'session_id' => $session->id,
            'status' => 'cancelled',
            'notified_students' => $enrolments->count(),
        ]]);
    }

    private function buildTeachingSummary(int $userId, $seasonId = null): array
    {
        $query = CourseClass::where('instructor_id', $userId)
            ->with(['course.subject', 'course.season', 'centre', 'classroom', 'sessions']);

        if ($seasonId) {
            $query->whereHas('course', fn ($q) => $q->where('season_id', $seasonId));
        }

        $classes = $query->orderByDesc('start_date')->get();

        $classes->each(fn ($class) => $class->setAttribute('hours', $this->classHourLedger($class)));

        return [
            'total_classes' => $classes->count(),
            'total_sessions' => $classes->sum(fn ($c) => $c->sessions->count()),
            'total_target_hours' => round($classes->sum(fn ($c) => $c->hours['target']), 2),
            'total_delivered_hours' => round($classes->sum(fn ($c) => $c->hours['delivered']), 2),
            'total_remaining_hours' => round($classes->sum(fn ($c) => $c->hours['remaining']), 2),
            'total_makeup_hours' => round($classes->sum(fn ($c) => $c->hours['makeup_needed']), 2),
            'classes' => $classes,
        ];
    }

    private function classHourLedger(CourseClass $class): array
    {
        $target = (float) ($class->course?->subject?->total_hours ?? 0);

        $delivered = 0.0;
        $scheduled = 0.0;
        $cancelled = 0.0;

        foreach ($class->sessions as $session) {
            $hours = $this->sessionHours($session);

            match ($session->status) {
                'completed' => $delivered += $hours,
                'cancelled' => $cancelled += $hours,
                default => $scheduled += $hours,
            };
        }

        $planned = $delivered + $scheduled;

        return [
            'target' => round($target, 2),
            'delivered' => round($delivered, 2),
            'scheduled' => round($scheduled, 2),
            'cancelled' => round($cancelled, 2),
            'remaining' => round(max(0, $target - $delivered), 2),
            'makeup_needed' => round(max(0, $target - $planned), 2),
        ];
    }

    private function sessionHours(ClassSession $session): float
    {
        if (! $session->start_time || ! $session->end_time) {
            return 0;
        }

        $start = strtotime((string) $session->start_time);
        $end = strtotime((string) $session->end_time);

        return $end > $start ? round(($end - $start) / 3600, 2) : 0;
    }

    public function generateContract(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer'],
            'instructor_id' => ['required', 'integer'],
        ]);

        $class = CourseClass::findOrFail($request->integer('class_id'));

        $contract = InstructorContract::updateOrCreate(
            [
                'class_id' => $class->id,
                'instructor_id' => $request->integer('instructor_id'),
            ],
            [
                'status' => 'draft',
            ]
        );

        return response()->json(['data' => $contract], 201);
    }

    public function signInSheet(int $id): JsonResponse
    {
        $instructor = InstructorProfile::findOrFail($id);

        return response()->json(['data' => $this->buildSignInSheet($instructor->user_id)]);
    }

    public function signInSheetMe(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->buildSignInSheet($request->user()->id)]);
    }

    private function buildSignInSheet(int $userId)
    {
        $classes = CourseClass::where('instructor_id', $userId)
            ->where('status', 'published')
            ->with(['course.subject', 'centre', 'classroom', 'sessions' => function ($q) {
                $q->orderBy('date')->orderBy('start_time');
            }])
            ->get();

        return $classes->map(function ($class) {
            return [
                'class_id' => $class->id,
                'class_code' => $class->class_code,
                'subject' => $class->course?->subject?->name,
                'centre' => $class->centre?->name,
                'classroom' => $class->classroom?->name,
                'sessions' => $class->sessions->map(function ($s) {
                    return [
                        'session_no' => $s->session_no,
                        'date' => $s->date,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ];
                }),
            ];
        });
    }
}
