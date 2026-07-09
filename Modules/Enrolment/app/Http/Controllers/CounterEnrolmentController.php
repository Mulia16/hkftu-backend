<?php

namespace Modules\Enrolment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Enrolment\DTOs\StoreCounterEnrolmentData;
use Modules\Enrolment\Services\CounterEnrolmentService;

class CounterEnrolmentController extends Controller
{
    public function __construct(
        private CounterEnrolmentService $counterService,
        private AuditLogger $auditLogger,
    ) {}

    public function store(StoreCounterEnrolmentData $data, Request $request): JsonResponse
    {
        $staffId = $request->user()->id;

        $result = $this->counterService->enrol($data, $staffId);

        $this->auditLogger->record('counter_enrolment.create', 'enrolment', $result['enrolment']->id, after: [
            'learner_id' => $data->learner_id,
            'class_id' => $data->class_id,
            'payment_method' => $data->payment_method,
            'receipt_no' => $result['receipt_no'],
        ]);

        return response()->json(['data' => $result], 201);
    }
}
