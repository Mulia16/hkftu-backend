<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->references('id')->on('auth.users')->cascadeOnDelete();
            $table->string('staff_no')->unique();
            $table->unsignedBigInteger('centre_id')->nullable();
            $table->string('department')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.staff_profiles');
    }
};
