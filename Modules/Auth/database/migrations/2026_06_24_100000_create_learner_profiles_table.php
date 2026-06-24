<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.learner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('auth.users')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_zh')->nullable();
            $table->string('id_type')->nullable()->comment('HKID, passport, etc.');
            $table->string('id_no_encrypted')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('membership_no')->nullable();
            $table->string('membership_status')->default('none')->comment('none, pending, active, expired');
            $table->timestamp('membership_verified_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique('user_id');
            $table->index('membership_no');
            $table->index('membership_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.learner_profiles');
    }
};
