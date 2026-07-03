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
        {--force : Force the operation to run when in production}';

    protected $description = 'Drop all tables including custom schemas, then migrate and optionally seed';

    private array $customSchemas = [
        'auth',
        'course_catalogue',
        'class_scheduling',
    ];

    public function handle(): int
    {
        $this->info('Dropping custom schemas...');

        foreach ($this->customSchemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS \"$schema\" CASCADE;");
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
