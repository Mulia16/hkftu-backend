<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE course_catalogue.subjects ALTER COLUMN lesson_hours TYPE decimal(6,2)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE course_catalogue.subjects ALTER COLUMN lesson_hours TYPE decimal(4,2)');
    }
};
