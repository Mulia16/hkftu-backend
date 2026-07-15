<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:fresh')]
class DatabaseFresh extends Command
{
    protected $signature = 'db:fresh
        {--seed : Seed the database after dropping and migrating}
        {--seeder= : The class name of the root seeder}
        {--force : Force the operation to run in production}';

    protected $description = 'Drop all tables including custom PostgreSQL schemas, then migrate and optionally seed';

    private array $customSchemas = [
        'auth',
        'course_catalogue',
        'class_scheduling',
        'enrolment',
        'attendance',
        'payment',
        'certificate',
        'instructor_finance',
    ];

    public function handle(): int
    {
        $database = DB::getDatabaseName();
        $this->info("Terminating active connections to <comment>$database</comment>...");

        DB::statement("
            SELECT pg_terminate_backend(pid)
            FROM pg_stat_activity
            WHERE datname = '$database'
              AND pid <> pg_backend_pid()
        ");

        $this->info('Dropping custom schemas...');

        foreach ($this->customSchemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS $schema CASCADE");
            $this->line("  Dropped schema: <comment>$schema</comment>");
        }

        $this->call('migrate:fresh', [
            '--seed' => $this->option('seed'),
            '--seeder' => $this->option('seeder'),
            '--force' => $this->option('force'),
        ]);

        return static::SUCCESS;
    }
}
