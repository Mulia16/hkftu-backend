<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.data_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->references('id')->on('auth.users')->nullOnDelete();
            $table->string('resource_type');
            $table->string('resource_id')->nullable();
            $table->string('action')->comment('view, export, print');
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('actor_user_id');
            $table->index('resource_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.data_access_logs');
    }
};
