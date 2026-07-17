<?php

namespace Modules\Payment\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Enrolment\Models\Enrolment;
use Modules\Payment\DTOs\ManualUploadData;
use Modules\Payment\DTOs\StorePaymentIntentData;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Models\PaymentTransaction;

class PaymentService
{
    public function __construct(
        private ReceiptPdfService $receiptPdfService,
    ) {}
    public function createIntent(StorePaymentIntentData $data, ?int $createdBy = null): PaymentIntent
    {
        $enrolment = Enrolment::findOrFail($data->enrolment_id);

        return PaymentIntent::create([
            'enrolment_id' => $enrolment->id,
            'amount' => $this->resolveAmount($enrolment),
            'currency' => 'HKD',
            'method' => $data->method,
            'status' => 'pending',
            'gateway' => $data->method === 'razerms' ? 'razerms' : null,
            'expires_at' => $data->method === 'razerms' ? now()->addMinutes(30) : null,
        ]);
    }

    public function uploadProof(ManualUploadData $data, int $userId): PaymentTransaction
    {
        $intent = PaymentIntent::findOrFail($data->payment_intent_id);

        if (! $intent->isPending()) {
            throw new \RuntimeException('Payment intent is not pending.');
        }

        $existing = PaymentTransaction::where('payment_intent_id', $intent->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            throw new \RuntimeException('A pending payment proof already exists for this intent.');
        }

        $path = $data->payment_proof->store('payment-proofs', 'public');

        return PaymentTransaction::create([
            'payment_intent_id' => $intent->id,
            'status' => 'pending',
            'payment_proof' => $path,
        ]);
    }

    public function approve(int $transactionId, int $adminId): PaymentTransaction
    {
        return DB::transaction(function () use ($transactionId, $adminId) {
            $transaction = PaymentTransaction::findOrFail($transactionId);

            if ($transaction->status !== 'pending') {
                throw new \RuntimeException('Transaction is not pending.');
            }

            $transaction->update([
                'status' => 'paid',
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            $intent = $transaction->paymentIntent;
            $intent->update(['status' => 'paid']);

            $enrolment = $intent->enrolment;
            if ($enrolment && $enrolment->status !== 'confirmed') {
                $enrolment->update(['status' => 'confirmed']);
            }

            $receiptNo = ReceiptService::generateReceiptNumber();

            $receipt = $intent->receipt()->create([
                'receipt_no' => $receiptNo,
                'enrolment_id' => $intent->enrolment_id,
                'amount' => $intent->amount,
                'issued_at' => now(),
                'issued_by' => $adminId,
                'status' => 'issued',
            ]);

            $this->receiptPdfService->generate($receipt);

            return $transaction->fresh(['paymentIntent.receipt', 'approver']);
        });
    }

    public function reject(int $transactionId, int $adminId, string $reason): PaymentTransaction
    {
        $transaction = PaymentTransaction::findOrFail($transactionId);

        if ($transaction->status !== 'pending') {
            throw new \RuntimeException('Transaction is not pending.');
        }

        $transaction->update([
            'status' => 'failed',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'reject_reason' => $reason,
        ]);

        $intent = $transaction->paymentIntent;
        $intent->update(['status' => 'failed']);

        return $transaction->fresh(['approver']);
    }

    public function listAll(?string $status = null, ?int $enrolmentId = null): LengthAwarePaginator
    {
        return PaymentIntent::with(['enrolment.learner', 'enrolment.courseClass.course.subject', 'transactions', 'receipt'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($enrolmentId, fn ($q) => $q->where('enrolment_id', $enrolmentId))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    public function listForLearner(int $learnerId): LengthAwarePaginator
    {
        return PaymentIntent::with(['enrolment.courseClass.course.subject', 'transactions', 'receipt'])
            ->whereHas('enrolment', fn ($q) => $q->where('learner_id', $learnerId))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    private function resolveAmount(Enrolment $enrolment): float
    {
        if ($enrolment->price_snapshot_json && isset($enrolment->price_snapshot_json['total'])) {
            return (float) $enrolment->price_snapshot_json['total'];
        }

        $class = $enrolment->courseClass;
        if ($class && $class->course && $class->course->subject) {
            $subject = $class->course->subject;

            return (float) $subject->tuition_fee + (float) $subject->material_fee;
        }

        return 0;
    }

    public function confirmGatewayPayment(PaymentIntent $intent, string $gatewayTxnId): void
    {
        DB::transaction(function () use ($intent, $gatewayTxnId) {
            $intent->update(['status' => 'paid']);

            PaymentTransaction::create([
                'payment_intent_id' => $intent->id,
                'gateway_txn_id' => $gatewayTxnId,
                'status' => 'paid',
                'verified' => true,
                'received_at' => now(),
            ]);

            $enrolment = $intent->enrolment;
            if ($enrolment && $enrolment->status !== 'confirmed') {
                $enrolment->update(['status' => 'confirmed']);
            }

            $receiptNo = ReceiptService::generateReceiptNumber();

            $receipt = $intent->receipt()->create([
                'receipt_no' => $receiptNo,
                'enrolment_id' => $intent->enrolment_id,
                'amount' => $intent->amount,
                'issued_at' => now(),
                'status' => 'issued',
            ]);

            $this->receiptPdfService->generate($receipt);
        });
    }
}
