<?php

namespace Modules\Enrolment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use App\Support\Ownership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Services\AuditLogger;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\DTOs\StoreEnrolmentData;
use Modules\Enrolment\Models\Enrolment;
use Modules\Enrolment\Services\EligibilityService;
use Modules\Enrolment\Services\SeatReservationService;
use Modules\Enrolment\Services\WaitlistService;

class EnrolmentController extends Controller
{
    public function __construct(
        private SeatReservationService $reservationService,
        private EligibilityService $eligibilityService,
        private WaitlistService $waitlistService,
        private AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Enrolment::with(['courseClass.course.subject', 'learner', 'creator'])
            ->when($request->centre_id, fn ($q) => $q->whereHas('courseClass', fn ($q) => $q->where('centre_id', $request->centre_id)))
            ->when($request->season_id, fn ($q) => $q->whereHas('courseClass.course', fn ($q) => $q->where('season_id', $request->season_id)))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->learner_id, fn ($q) => $q->where('learner_id', $request->learner_id))
            ->orderByDesc('created_at');

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }

    public function store(StoreEnrolmentData $data, Request $request): JsonResponse
    {
        $user = $request->user();

        if (! Ownership::isStaff($user)) {
            if (! in_array($data->channel, ['online_member', 'online_public'], true)) {
                return Ownership::forbidden('Only staff can create manual or counter enrolments.');
            }

            if (! Ownership::ownsLearner($user, $data->learner_id)) {
                return Ownership::forbidden('You can only enrol a learner profile you own.');
            }
        }

        $class = CourseClass::findOrFail($data->class_id);
        $learner = LearnerProfile::findOrFail($data->learner_id);

        $eligibility = $this->eligibilityService->check($class, $learner, $data->channel);

        if (! $eligibility['allowed']) {
            return ApiError::respond('ELIGIBILITY_FAILED', 'Eligibility check failed.', 422, [
                'reasons' => $eligibility['reasons'],
            ]);
        }

        if ($data->reservation_id) {
            $this->reservationService->confirm($data->reservation_id);
        }

        $enrolment = Enrolment::create([
            'class_id' => $class->id,
            'learner_id' => $learner->id,
            'reservation_id' => $data->reservation_id,
            'status' => $data->channel === 'manual' ? 'confirmed' : 'pending',
            'channel' => $data->channel,
            'price_snapshot_json' => $eligibility['pricing'],
            'member_snapshot_json' => [
                'membership_status' => $learner->membership_status,
                'membership_no' => $learner->membership_no,
            ],
            'created_by' => $request->user()?->id,
        ]);

        $this->auditLogger->record('enrolment.create', 'enrolment', $enrolment->id, after: $enrolment->toArray());

        return response()->json(['data' => $enrolment->load(['courseClass.course.subject', 'learner'])], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $enrolment = Enrolment::with(['courseClass.course.subject', 'learner', 'reservation', 'creator'])->findOrFail($id);

        if (! Ownership::canAccessLearner($request->user(), $enrolment->learner_id)) {
            return Ownership::forbidden();
        }

        return response()->json(['data' => $enrolment]);
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        $enrolment = Enrolment::findOrFail($id);

        if ($enrolment->status !== 'pending') {
            return ApiError::respond('INVALID_STATUS', 'Only pending enrolments can be confirmed.', 422);
        }

        $before = $enrolment->toArray();
        $enrolment->update(['status' => 'confirmed']);

        $enrolment->reservation()
            ->where('status', 'active')
            ->update(['status' => 'converted']);

        $this->auditLogger->record('enrolment.confirm', 'enrolment', $id, before: $before, after: $enrolment->toArray());

        return response()->json(['data' => $enrolment]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $enrolment = Enrolment::findOrFail($id);

        if (! Ownership::canAccessLearner($request->user(), $enrolment->learner_id)) {
            return Ownership::forbidden();
        }

        if (! in_array($enrolment->status, ['pending', 'confirmed'])) {
            return ApiError::respond('INVALID_STATUS', 'Cannot cancel enrolment in current status.', 422);
        }

        $before = $enrolment->toArray();
        $enrolment->update(['status' => 'cancelled']);

        $this->auditLogger->record('enrolment.cancel', 'enrolment', $id, before: $before, after: $enrolment->toArray());

        $class = $enrolment->courseClass;
        if ($class) {
            $this->waitlistService->offerNextSeat($class);
        }

        return response()->json(['data' => $enrolment]);
    }
}
