<?php

namespace Modules\Enrolment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Services\AuditLogger;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\DTOs\CancelSeatReservationData;
use Modules\Enrolment\DTOs\StoreSeatReservationData;
use Modules\Enrolment\Models\SeatReservation;
use Modules\Enrolment\Services\EligibilityService;
use Modules\Enrolment\Services\SeatReservationService;

class SeatReservationController extends Controller
{
    public function __construct(
        private SeatReservationService $reservationService,
        private EligibilityService $eligibilityService,
        private AuditLogger $auditLogger,
    ) {}

    public function store(StoreSeatReservationData $data, Request $request): JsonResponse
    {
        $class = CourseClass::findOrFail($data->class_id);
        $learner = LearnerProfile::findOrFail($data->learner_id);

        $eligibility = $this->eligibilityService->check($class, $learner, $data->channel);

        if (! $eligibility['allowed']) {
            return ApiError::respond('ELIGIBILITY_FAILED', 'Eligibility check failed.', 422, [
                'reasons' => $eligibility['reasons'],
            ]);
        }

        try {
            $reservation = $this->reservationService->reserve(
                class: $class,
                learnerId: $learner->id,
                channel: $data->channel,
                idempotencyKey: $request->header('Idempotency-Key'),
                amountSnapshot: $eligibility['pricing'],
                eligibilitySnapshot: $eligibility,
                ip: $request->ip(),
            );
        } catch (\RuntimeException $e) {
            return ApiError::respond('RESERVATION_FAILED', $e->getMessage(), 409);
        }

        $this->auditLogger->record('seat_reservation.create', 'seat_reservation', $reservation->id, after: [
            'class_id' => $class->id,
            'learner_id' => $learner->id,
            'channel' => $data->channel,
        ]);

        return response()->json([
            'data' => [
                'reservation_id' => $reservation->id,
                'status' => $reservation->status,
                'expires_at' => $reservation->expires_at->toIso8601String(),
                'amount' => $eligibility['pricing'],
                'next_action' => 'CREATE_PAYMENT_INTENT',
            ],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $reservation = SeatReservation::with(['courseClass.course.subject', 'learner'])->findOrFail($id);

        return response()->json(['data' => $reservation]);
    }

    public function cancel(CancelSeatReservationData $data, int $id): JsonResponse
    {
        try {
            $reservation = $this->reservationService->cancel($id, $data->learner_id);
        } catch (\Exception $e) {
            return ApiError::respond('CANCEL_FAILED', 'Cannot cancel reservation.', 422);
        }

        $this->auditLogger->record('seat_reservation.cancel', 'seat_reservation', $id);

        return response()->json(['data' => $reservation]);
    }
}
