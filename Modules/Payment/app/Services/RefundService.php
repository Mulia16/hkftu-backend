<?php

namespace Modules\Payment\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Enrolment\Models\Enrolment;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Models\Refund;

class RefundService
{
    public function request(int $enrolmentId, float $amount, string $reason, int $requesterId): Refund
    {
        $enrolment = Enrolment::findOrFail($enrolmentId);

        if (! in_array($enrolment->status, ['confirmed', 'transferred'])) {
            throw new \RuntimeException('Only confirmed or transferred enrolments can be refunded.');
        }

        $intent = PaymentIntent::where('enrolment_id', $enrolmentId)
            ->where('status', 'paid')
            ->firstOrFail();

        if ($amount > (float) $intent->amount) {
            throw new \RuntimeException('Refund amount cannot exceed paid amount.');
        }

        return Refund::create([
            'enrolment_id' => $enrolmentId,
            'payment_intent_id' => $intent->id,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'requested',
            'requested_by' => $requesterId,
        ]);
    }

    public function approve(int $refundId, int $approverId): Refund
    {
        return DB::transaction(function () use ($refundId, $approverId) {
            $refund = Refund::findOrFail($refundId);

            if ($refund->status !== 'requested') {
                throw new \RuntimeException('Refund is not in requested status.');
            }

            $refund->update([
                'status' => 'approved',
                'approved_by' => $approverId,
            ]);

            return $refund->fresh(['enrolment.learner', 'paymentIntent', 'requester', 'approver']);
        });
    }

    public function execute(int $refundId, int $executorId): Refund
    {
        return DB::transaction(function () use ($refundId) {
            $refund = Refund::findOrFail($refundId);

            if ($refund->status !== 'approved') {
                throw new \RuntimeException('Refund must be approved before execution.');
            }

            $refund->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $intent = $refund->paymentIntent;
            if ($intent) {
                $intent->update(['status' => 'refunded']);
            }

            $enrolment = $refund->enrolment;
            if ($enrolment && $refund->amount >= (float) $intent->amount) {
                $enrolment->update(['status' => 'cancelled']);
            }

            return $refund->fresh(['enrolment.learner', 'paymentIntent', 'requester', 'approver']);
        });
    }

    public function reject(int $refundId, int $approverId, string $reason): Refund
    {
        $refund = Refund::findOrFail($refundId);

        if ($refund->status !== 'requested') {
            throw new \RuntimeException('Refund is not in requested status.');
        }

        $refund->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'rejection_reason' => $reason,
        ]);

        return $refund->fresh(['enrolment.learner', 'paymentIntent', 'requester', 'approver']);
    }

    public function listAll(?string $status = null): LengthAwarePaginator
    {
        return Refund::with(['enrolment.learner', 'enrolment.courseClass.course.subject', 'paymentIntent', 'requester', 'approver'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    public function listForLearner(int $learnerId): LengthAwarePaginator
    {
        return Refund::with(['enrolment.courseClass.course.subject', 'paymentIntent'])
            ->whereHas('enrolment', fn ($q) => $q->where('learner_id', $learnerId))
            ->orderByDesc('created_at')
            ->paginate(25);
    }
}
