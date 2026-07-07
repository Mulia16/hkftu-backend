<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Payment\DTOs\VerifyPaymentData;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Services\PaymentService;

class AdminPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->listAll(
            $request->input('status'),
            $request->integer('enrolment_id'),
        );

        return response()->json($payments);
    }

    public function show(int $id): JsonResponse
    {
        $intent = PaymentIntent::with([
            'enrolment.learner',
            'enrolment.courseClass.course.subject',
            'transactions.approver',
            'receipt',
        ])->findOrFail($id);

        return response()->json(['data' => $intent]);
    }

    public function verify(int $transactionId, VerifyPaymentData $data, Request $request): JsonResponse
    {
        $adminId = $request->user()->id;

        if ($data->action === 'approve') {
            $transaction = $this->paymentService->approve($transactionId, $adminId);
            $this->auditLogger->record('payment.approve', 'payment_transaction', $transactionId, after: $transaction->toArray());
        } else {
            $transaction = $this->paymentService->reject($transactionId, $adminId, $data->reject_reason);
            $this->auditLogger->record('payment.reject', 'payment_transaction', $transactionId, after: $transaction->toArray());
        }

        return response()->json(['data' => $transaction]);
    }
}
