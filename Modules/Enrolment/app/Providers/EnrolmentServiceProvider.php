<?php

namespace Modules\Enrolment\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Enrolment\Jobs\ExpireWaitlistOffers;
use Modules\Enrolment\Jobs\ReleaseExpiredReservations;
use Nwidart\Modules\Support\ModuleServiceProvider;

class EnrolmentServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Enrolment';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'enrolment';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->job(new ReleaseExpiredReservations)->everyMinute();
        $schedule->job(new ExpireWaitlistOffers)->everyMinute();
    }
}
