<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->references('id')->on('auth.users')->nullOnDelete();
            $table->string('action');
            $table->string('resource_type');
            $table->string('resource_id')->nullable();
            $table->jsonb('before_json')->nullable();
            $table->jsonb('after_json')->nullable();
            $table->string('ip')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.audit_logs');
    }
};
