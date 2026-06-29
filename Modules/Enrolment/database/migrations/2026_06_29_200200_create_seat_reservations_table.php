<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolment.seat_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes')->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->string('channel')->comment('online_member, online_public, counter, proxy');
            $table->string('status')->default('active')->comment('active, confirmed, expired, cancelled');
            $table->timestamp('expires_at');
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('amount_snapshot_json')->nullable();
            $table->json('eligibility_snapshot_json')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();

            $table->index('class_id');
            $table->index('learner_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index(['class_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment.seat_reservations');
    }
};
