<?php

namespace Modules\Enrolment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Enrolment\DTOs\StoreTransferData;
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

    public function show(int $id): JsonResponse
    {
        $transfer = Transfer::with(['oldEnrolment.learner', 'oldEnrolment.courseClass.course.subject', 'newEnrolment', 'newClass.course.subject', 'requester', 'approver'])->findOrFail($id);

        return response()->json(['data' => $transfer]);
    }
}
