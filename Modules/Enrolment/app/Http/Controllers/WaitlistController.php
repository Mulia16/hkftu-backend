<?php

namespace Modules\Enrolment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\DTOs\StoreWaitlistData;
use Modules\Enrolment\Models\Waitlist;
use Modules\Enrolment\Services\SeatReservationService;
use Modules\Enrolment\Services\WaitlistService;

class WaitlistController extends Controller
{
    public function __construct(
        private SeatReservationService $reservationService,
        private WaitlistService $waitlistService,
        private AuditLogger $auditLogger,
    ) {}

    public function store(StoreWaitlistData $data): JsonResponse
    {
        $class = CourseClass::findOrFail($data->class_id);
        $available = $this->reservationService->calculateAvailableSeats($class);

        if ($available > 0) {
            return ApiError::respond('SEATS_AVAILABLE', 'Seats are still available. Use reservation instead.', 422);
        }

        $existing = Waitlist::where('class_id', $class->id)
            ->where('learner_id', $data->learner_id)
            ->whereIn('status', ['waiting', 'offered'])
            ->exists();

        if ($existing) {
            return ApiError::respond('ALREADY_WAITLISTED', 'You are already on the waitlist for this class.', 422);
        }

        $nextPosition = Waitlist::where('class_id', $class->id)->max('position') + 1;

        $waitlist = Waitlist::create([
            'class_id' => $class->id,
            'learner_id' => $data->learner_id,
            'position' => $nextPosition,
            'status' => 'waiting',
        ]);

        $this->auditLogger->record('waitlist.join', 'waitlist', $waitlist->id, after: [
            'class_id' => $class->id,
            'position' => $nextPosition,
        ]);

        return response()->json(['data' => $waitlist], 201);
    }

    public function offer(Request $request, int $id): JsonResponse
    {
        $waitlist = Waitlist::where('status', 'waiting')->findOrFail($id);

        $waitlist->update([
            'status' => 'offered',
            'offered_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        $this->auditLogger->record('waitlist.offer', 'waitlist', $id);

        return response()->json(['data' => $waitlist]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $waitlist = $this->waitlistService->accept($id, $user->learnerProfile->id);
        } catch (\RuntimeException $e) {
            return ApiError::respond('OFFER_EXPIRED', $e->getMessage(), 422);
        }

        $this->auditLogger->record('waitlist.accept', 'waitlist', $id);

        return response()->json(['data' => $waitlist]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $waitlist = $this->waitlistService->cancel($id, $user->learnerProfile->id);

        $this->auditLogger->record('waitlist.cancel', 'waitlist', $id);

        return response()->json(['data' => $waitlist]);
    }

    public function myWaitlists(Request $request): JsonResponse
    {
        $user = $request->user();

        $waitlists = Waitlist::with('courseClass.course.subject')
            ->where('learner_id', $user->learnerProfile?->id)
            ->whereIn('status', ['waiting', 'offered'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $waitlists]);
    }
}
