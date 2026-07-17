<?php

namespace Modules\Enrolment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Waitlist;
use Modules\Enrolment\Services\WaitlistService;

class ExpireWaitlistOffers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(WaitlistService $waitlistService): void
    {
        $affectedClassIds = Waitlist::where('status', 'offered')
            ->where('expires_at', '<=', now())
            ->pluck('class_id')
            ->unique();

        $expired = Waitlist::where('status', 'offered')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        if ($expired > 0) {
            Log::info("Expired {$expired} waitlist offers.");

            foreach ($affectedClassIds as $classId) {
                $class = CourseClass::find($classId);
                if ($class) {
                    $waitlistService->offerNextSeat($class);
                }
            }
        }
    }
}
