<?php

namespace Modules\Enrolment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Ownership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Enrolment\DTOs\StoreTransferData;
use Modules\Enrolment\Models\Enrolment;
use Modules\Enrolment\Models\Transfer;
use Modules\Enrolment\Services\TransferService;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
        private AuditLogger $auditLogger,
    ) {}

    public function store(StoreTransferData $data, Request $request): JsonResponse
    {
        if (! Ownership::isStaff($request->user())) {
            $enrolment = Enrolment::find($data->enrolment_id);
            if (! $enrolment || ! Ownership::ownsLearner($request->user(), $enrolment->learner_id)) {
                return Ownership::forbidden('You can only request a transfer for your own enrolment.');
            }
        }

        $transfer = $this->transferService->request(
            $data->enrolment_id,
            $data->new_class_id,
            $request->user()->id,
            $data->reason,
        );

        $this->auditLogger->record('transfer.request', 'transfer', $transfer->id, after: $transfer->toArray());

        return response()->json(['data' => $transfer], 201);
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $transfer = $this->transferService->approve($id, $request->user()->id);

        $this->auditLogger->record('transfer.approve', 'transfer', $id, after: $transfer->toArray());

        return response()->json(['data' => $transfer]);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $transfer = $this->transferService->reject($id, $request->user()->id, $request->input('reason'));

        $this->auditLogger->record('transfer.reject', 'transfer', $id, after: $transfer->toArray());

        return response()->json(['data' => $transfer]);
    }

    public function index(Request $request): JsonResponse
    {
        $transfers = $this->transferService->listAll($request->input('status'));

        return response()->json($transfers);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $transfer = Transfer::with(['oldEnrolment.learner', 'oldEnrolment.courseClass.course.subject', 'newEnrolment', 'newClass.course.subject', 'requester', 'approver'])->findOrFail($id);

        if (! Ownership::canAccessLearner($request->user(), $transfer->oldEnrolment?->learner_id)) {
            return Ownership::forbidden();
        }

        return response()->json(['data' => $transfer]);
    }
}
