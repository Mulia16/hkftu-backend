<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.member_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_profile_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->string('membership_no')->nullable();
            $table->string('status')->default('pending')->comment('pending, verified, failed, expired');
            $table->string('source')->default('mock')->comment('mock, hq_api, manual');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('verified_by')->nullable()->comment('user_id or system');
            $table->text('failure_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('learner_profile_id');
            $table->index('status');
            $table->index('membership_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.member_verifications');
    }
};
