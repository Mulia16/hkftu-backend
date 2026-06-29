<?php

namespace Modules\Enrolment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Enrolment\Services\SeatReservationService;

class ReleaseExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SeatReservationService $service): void
    {
        $released = $service->releaseExpired();

        if ($released > 0) {
            Log::info("Released {$released} expired seat reservations.");
        }
    }
}
