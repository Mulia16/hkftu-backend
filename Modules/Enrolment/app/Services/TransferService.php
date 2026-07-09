<?php

namespace Modules\Enrolment\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;
use Modules\Enrolment\Models\Transfer;

class TransferService
{
    public function __construct(
        private SeatReservationService $seatService,
        private EligibilityService $eligibilityService,
    ) {}

    public function request(int $enrolmentId, int $newClassId, int $requesterId, ?string $reason = null): Transfer
    {
        $enrolment = Enrolment::with('courseClass.course.subject')->findOrFail($enrolmentId);

        if (! in_array($enrolment->status, ['confirmed'])) {
            throw new \RuntimeException('Only confirmed enrolments can be transferred.');
        }

        $newClass = CourseClass::findOrFail($newClassId);

        if ($newClass->start_date && $newClass->start_date->isPast()) {
            throw new \RuntimeException('Cannot transfer to a class that has already started.');
        }

        if ($newClass->id === $enrolment->class_id) {
            throw new \RuntimeException('Cannot transfer to the same class.');
        }

        $oldPrice = $this->resolvePrice($enrolment);
        $newPrice = $this->resolveClassPrice($newClass);
        $difference = $newPrice - $oldPrice;

        return Transfer::create([
            'old_enrolment_id' => $enrolmentId,
            'new_class_id' => $newClassId,
            'price_difference' => $difference,
            'status' => 'requested',
            'reason' => $reason,
            'requested_by' => $requesterId,
        ]);
    }

    public function approve(int $transferId, int $approverId): Transfer
    {
        return DB::transaction(function () use ($transferId, $approverId) {
            $transfer = Transfer::findOrFail($transferId);

            if ($transfer->status !== 'requested') {
                throw new \RuntimeException('Transfer is not in requested status.');
            }

            $this->seatService->reserve(
                CourseClass::findOrFail($transfer->new_class_id),
                $transfer->oldEnrolment->learner_id,
                'counter',
            );

            $oldEnrolment = $transfer->oldEnrolment;
            $oldPrice = $this->resolvePrice($oldEnrolment);
            $newPrice = $this->resolveClassPrice(CourseClass::findOrFail($transfer->new_class_id));

            $newEnrolment = Enrolment::create([
                'class_id' => $transfer->new_class_id,
                'learner_id' => $oldEnrolment->learner_id,
                'status' => 'confirmed',
                'channel' => $oldEnrolment->channel,
                'price_snapshot_json' => [
                    'base_price' => $newPrice,
                    'member_discount' => 0,
                    'total' => $newPrice,
                ],
                'member_snapshot_json' => $oldEnrolment->member_snapshot_json,
                'created_by' => $approverId,
            ]);

            $oldEnrolment->update(['status' => 'transferred']);

            $transfer->update([
                'status' => 'completed',
                'new_enrolment_id' => $newEnrolment->id,
                'approved_by' => $approverId,
                'completed_at' => now(),
            ]);

            return $transfer->fresh(['oldEnrolment', 'newEnrolment', 'newClass', 'requester', 'approver']);
        });
    }

    public function reject(int $transferId, int $approverId, string $reason): Transfer
    {
        $transfer = Transfer::findOrFail($transferId);

        if ($transfer->status !== 'requested') {
            throw new \RuntimeException('Transfer is not in requested status.');
        }

        $transfer->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'rejection_reason' => $reason,
        ]);

        return $transfer->fresh(['oldEnrolment', 'newClass', 'requester', 'approver']);
    }

    public function listAll(?string $status = null): LengthAwarePaginator
    {
        return Transfer::with(['oldEnrolment.learner', 'oldEnrolment.courseClass.course.subject', 'newClass.course.subject', 'requester', 'approver'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    public function listForLearner(int $learnerId): LengthAwarePaginator
    {
        return Transfer::with(['oldEnrolment.courseClass.course.subject', 'newClass.course.subject'])
            ->whereHas('oldEnrolment', fn ($q) => $q->where('learner_id', $learnerId))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    private function resolvePrice(Enrolment $enrolment): float
    {
        if ($enrolment->price_snapshot_json && isset($enrolment->price_snapshot_json['total'])) {
            return (float) $enrolment->price_snapshot_json['total'];
        }

        return $this->resolveClassPrice($enrolment->courseClass);
    }

    private function resolveClassPrice(CourseClass $class): float
    {
        $subject = $class->course?->subject;
        if (! $subject) {
            return 0;
        }

        return (float) $subject->tuition_fee + (float) $subject->material_fee;
    }
}
