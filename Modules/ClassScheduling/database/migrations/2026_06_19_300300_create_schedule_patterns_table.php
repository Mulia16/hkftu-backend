<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_scheduling.schedule_patterns', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['weekly', 'one_off']);
            $table->json('days_of_week')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->json('overrides')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_scheduling.schedule_patterns');
    }
};
