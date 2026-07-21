<?php

namespace Modules\Reporting\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Reporting\Models\ReportRun;
use Modules\Reporting\Services\ReportService;

class ProcessReportRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reportRunId) {}

    public function handle(ReportService $service): void
    {
        $run = ReportRun::find($this->reportRunId);

        if ($run) {
            $service->executeReport($run);
        }
    }
}
