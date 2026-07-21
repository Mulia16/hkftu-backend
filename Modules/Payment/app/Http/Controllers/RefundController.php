<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Ownership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Enrolment\Models\Enrolment;
use Modules\Payment\DTOs\StoreRefundData;
use Modules\Payment\Models\Refund;
use Modules\Payment\Services\RefundService;

class RefundController extends Controller
{
    public function __construct(
        private RefundService $refundService,
        private AuditLogger $auditLogger,
    ) {}

    public function store(StoreRefundData $data, Request $request): JsonResponse
    {
        if (! Ownership::isStaff($request->user())) {
            $enrolment = Enrolment::find($data->enrolment_id);
            if (! $enrolment || ! Ownership::ownsLearner($request->user(), $enrolment->learner_id)) {
                return Ownership::forbidden('You can only request a refund for your own enrolment.');
            }
        }

        $refund = $this->refundService->request(
            $data->enrolment_id,
            $data->amount,
            $data->reason,
            $request->user()->id,
        );

        $this->auditLogger->record('refund.request', 'refund', $refund->id, after: $refund->toArray());

        return response()->json(['data' => $refund], 201);
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $refund = $this->refundService->approve($id, $request->user()->id);

        $this->auditLogger->record('refund.approve', 'refund', $id, after: $refund->toArray());

        return response()->json(['data' => $refund]);
    }

    public function execute(int $id, Request $request): JsonResponse
    {
        $refund = $this->refundService->execute($id, $request->user()->id);

        $this->auditLogger->record('refund.execute', 'refund', $id, after: $refund->toArray());

        return response()->json(['data' => $refund]);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $refund = $this->refundService->reject($id, $request->user()->id, $request->input('reason'));

        $this->auditLogger->record('refund.reject', 'refund', $id, after: $refund->toArray());

        return response()->json(['data' => $refund]);
    }

    public function index(Request $request): JsonResponse
    {
        $refunds = $this->refundService->listAll($request->input('status'));

        return response()->json($refunds);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $refund = Refund::with(['enrolment.learner', 'enrolment.courseClass.course.subject', 'paymentIntent', 'requester', 'approver'])->findOrFail($id);

        if (! Ownership::canAccessLearner($request->user(), $refund->enrolment?->learner_id)) {
            return Ownership::forbidden();
        }

        return response()->json(['data' => $refund]);
    }

    public function myRefunds(Request $request): JsonResponse
    {
        $learner = $request->user()->learnerProfile;
        if (! $learner) {
            return response()->json(['data' => []]);
        }

        $refunds = $this->refundService->listForLearner($learner->id);

        return response()->json($refunds);
    }
}
