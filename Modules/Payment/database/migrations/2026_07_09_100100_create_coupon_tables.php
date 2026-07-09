<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment.coupon_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('season_id')->nullable()->constrained('course_catalogue.seasons');
            $table->string('discount_type', 20)->default('fixed');
            $table->decimal('value', 10, 2)->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->jsonb('rules_json')->nullable();
            $table->integer('max_usage')->nullable();
            $table->integer('used_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('status');
            $table->index('code');
        });

        Schema::create('payment.coupon_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('payment.coupon_campaigns');
            $table->string('code')->unique();
            $table->string('code_hash');
            $table->string('status', 20)->default('active');
            $table->foreignId('assigned_to')->nullable()->constrained('auth.users');
            $table->integer('usage_limit')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('code');
        });

        Schema::create('payment.coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_code_id')->constrained('payment.coupon_codes');
            $table->foreignId('enrolment_id')->constrained('enrolment.enrolments');
            $table->foreignId('payment_intent_id')->nullable()->constrained('payment.payment_intents');
            $table->decimal('amount_discounted', 10, 2);
            $table->foreignId('redeemed_by')->constrained('auth.users');
            $table->timestamp('redeemed_at');
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();

            $table->index('coupon_code_id');
            $table->index('enrolment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment.coupon_redemptions');
        Schema::dropIfExists('payment.coupon_codes');
        Schema::dropIfExists('payment.coupon_campaigns');
    }
};
