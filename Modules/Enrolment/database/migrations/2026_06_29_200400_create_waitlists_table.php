<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolment.waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes')->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('status')->default('waiting')->comment('waiting, offered, accepted, expired, cancelled');
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('class_id');
            $table->index('learner_id');
            $table->index('status');
            $table->unique(['class_id', 'learner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment.waitlists');
    }
};
