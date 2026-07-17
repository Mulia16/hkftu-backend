<?php

namespace Modules\Enrolment\Services;

use Illuminate\Support\Facades\Log;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Waitlist;

class WaitlistService
{
    public function __construct(
        private SeatReservationService $reservationService,
    ) {}

    public function offerNextSeat(CourseClass $class): ?Waitlist
    {
        $available = $this->reservationService->calculateAvailableSeats($class);

        if ($available <= 0) {
            return null;
        }

        $next = Waitlist::where('class_id', $class->id)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->first();

        if (!$next) {
            return null;
        }

        $next->update([
            'status' => 'offered',
            'offered_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        Log::info("Offered seat to waitlist #{$next->id} for class {$class->id}");

        return $next->refresh();
    }

    public function accept(int $waitlistId, int $learnerId): Waitlist
    {
        $waitlist = Waitlist::where('id', $waitlistId)
            ->where('learner_id', $learnerId)
            ->where('status', 'offered')
            ->firstOrFail();

        if ($waitlist->expires_at && $waitlist->expires_at->isPast()) {
            $waitlist->update(['status' => 'expired']);
            throw new \RuntimeException('Waitlist offer has expired.');
        }

        $waitlist->update(['status' => 'accepted']);

        return $waitlist;
    }

    public function cancel(int $waitlistId, int $learnerId): Waitlist
    {
        $waitlist = Waitlist::where('id', $waitlistId)
            ->where('learner_id', $learnerId)
            ->whereIn('status', ['waiting', 'offered'])
            ->firstOrFail();

        $waitlist->update(['status' => 'cancelled']);

        return $waitlist;
    }
}
