<?php

namespace Modules\Enrolment\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\LearnerProfile;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\DTOs\StoreCounterEnrolmentData;
use Modules\Enrolment\Models\Enrolment;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Services\ReceiptService;

class CounterEnrolmentService
{
    public function __construct(
        private SeatReservationService $seatService,
        private EligibilityService $eligibilityService,
    ) {}

    public function enrol(StoreCounterEnrolmentData $data, int $staffId): array
    {
        $learner = LearnerProfile::findOrFail($data->learner_id);
        $class = CourseClass::findOrFail($data->class_id);

        $eligibility = $this->eligibilityService->check($class, $learner, 'counter');

        if (! $eligibility['allowed']) {
            throw new \RuntimeException('Eligibility failed: '.implode(', ', $eligibility['reasons']));
        }

        return DB::transaction(function () use ($data, $learner, $class, $staffId, $eligibility) {
            $this->seatService->reserve($class, $learner->id, 'counter');

            $pricing = $eligibility['pricing'];
            $total = $pricing['total'];

            $enrolment = Enrolment::create([
                'class_id' => $data->class_id,
                'learner_id' => $data->learner_id,
                'status' => 'confirmed',
                'channel' => 'counter',
                'price_snapshot_json' => $pricing,
                'member_snapshot_json' => [
                    'membership_status' => $learner->membership_status,
                ],
                'created_by' => $staffId,
            ]);

            $intent = PaymentIntent::create([
                'enrolment_id' => $enrolment->id,
                'amount' => $total,
                'currency' => 'HKD',
                'method' => $data->payment_method,
                'status' => 'paid',
            ]);

            $transaction = $intent->transactions()->create([
                'status' => 'paid',
                'approved_by' => $staffId,
                'approved_at' => now(),
            ]);

            $receiptNo = ReceiptService::generateReceiptNumber();

            $intent->receipt()->create([
                'receipt_no' => $receiptNo,
                'enrolment_id' => $enrolment->id,
                'amount' => $total,
                'issued_at' => now(),
                'issued_by' => $staffId,
                'status' => 'issued',
            ]);

            return [
                'enrolment' => $enrolment->fresh(['learner', 'courseClass.course.subject']),
                'payment_intent' => $intent->fresh('receipt'),
                'receipt_no' => $receiptNo,
            ];
        });
    }
}
