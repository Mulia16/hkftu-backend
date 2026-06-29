<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.member_pricing_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_profile_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->foreignId('member_verification_id')->nullable()->references('id')->on('auth.member_verifications')->nullOnDelete();
            $table->string('member_type')->nullable()->comment('ordinary, senior, student, corporate');
            $table->string('pricing_rule')->comment('member_price, public_price, discounted');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->json('applicable_seasons')->nullable();
            $table->json('applicable_centres')->nullable();
            $table->json('applicable_course_types')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('learner_profile_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.member_pricing_eligibilities');
    }
};
