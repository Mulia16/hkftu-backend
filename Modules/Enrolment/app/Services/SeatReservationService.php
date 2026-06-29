<?php

namespace Modules\Enrolment\Services;

use Illuminate\Support\Facades\DB;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;
use Modules\Enrolment\Models\SeatReservation;

class SeatReservationService
{
    public const DEFAULT_EXPIRY_MINUTES = 15;

    public function reserve(
        CourseClass $class,
        int $learnerId,
        string $channel,
        ?string $idempotencyKey = null,
        ?array $amountSnapshot = null,
        ?array $eligibilitySnapshot = null,
        ?string $ip = null,
    ): SeatReservation {
        $existing = $this->findExistingReservation($idempotencyKey, $learnerId, $class->id);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($class, $learnerId, $channel, $idempotencyKey, $amountSnapshot, $eligibilitySnapshot, $ip) {
            $locked = CourseClass::where('id', $class->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'published') {
                throw new \RuntimeException('Class is not available for reservation.');
            }

            $available = $this->calculateAvailableSeats($locked);

            if ($available <= 0) {
                throw new \RuntimeException('No seats available. Class is full.');
            }

            return SeatReservation::create([
                'class_id' => $locked->id,
                'learner_id' => $learnerId,
                'channel' => $channel,
                'status' => 'active',
                'expires_at' => now()->addMinutes(self::DEFAULT_EXPIRY_MINUTES),
                'idempotency_key' => $idempotencyKey,
                'amount_snapshot_json' => $amountSnapshot,
                'eligibility_snapshot_json' => $eligibilitySnapshot,
                'ip' => $ip,
            ]);
        });
    }

    public function calculateAvailableSeats(CourseClass $class): int
    {
        $confirmedCount = Enrolment::where('class_id', $class->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();

        $activeReservedCount = SeatReservation::where('class_id', $class->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        return max(0, $class->capacity - $confirmedCount - $activeReservedCount);
    }

    public function cancel(int $reservationId, int $learnerId): SeatReservation
    {
        $reservation = SeatReservation::where('id', $reservationId)
            ->where('learner_id', $learnerId)
            ->where('status', 'active')
            ->firstOrFail();

        $reservation->update(['status' => 'cancelled']);

        return $reservation;
    }

    public function confirm(int $reservationId): SeatReservation
    {
        $reservation = SeatReservation::where('id', $reservationId)
            ->where('status', 'active')
            ->firstOrFail();

        if ($reservation->isExpired()) {
            $reservation->update(['status' => 'expired']);
            throw new \RuntimeException('Reservation has expired.');
        }

        $reservation->update(['status' => 'confirmed']);

        return $reservation;
    }

    public function releaseExpired(): int
    {
        return SeatReservation::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    private function findExistingReservation(?string $idempotencyKey, int $learnerId, int $classId): ?SeatReservation
    {
        if ($idempotencyKey) {
            $existing = SeatReservation::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $existing;
            }
        }

        return SeatReservation::where('learner_id', $learnerId)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }
}
