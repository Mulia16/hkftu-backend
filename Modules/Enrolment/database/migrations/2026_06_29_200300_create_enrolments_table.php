<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolment.enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes')->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->references('id')->on('enrolment.seat_reservations')->nullOnDelete();
            $table->string('status')->default('pending')->comment('pending, confirmed, transferred, cancelled, withdrawn');
            $table->string('channel')->comment('online_member, online_public, counter, proxy, manual');
            $table->json('price_snapshot_json')->nullable();
            $table->json('member_snapshot_json')->nullable();
            $table->foreignId('created_by')->nullable()->references('id')->on('auth.users')->nullOnDelete();
            $table->timestamps();

            $table->index('class_id');
            $table->index('learner_id');
            $table->index('status');
            $table->index('reservation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment.enrolments');
    }
};
