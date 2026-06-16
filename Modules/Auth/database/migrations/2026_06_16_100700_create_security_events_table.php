<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.security_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('auth.users')->nullOnDelete();
            $table->string('event_type');
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('severity')->default('info');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.security_events');
    }
};
