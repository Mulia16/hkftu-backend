<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.member_status_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_profile_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->string('membership_no')->nullable();
            $table->string('status')->comment('active, expired, none, pending');
            $table->string('source')->default('mock')->comment('mock, hq_api, manual');
            $table->json('raw_response')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamps();

            $table->index('learner_profile_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.member_status_snapshots');
    }
};
