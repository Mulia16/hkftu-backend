<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('auth.users')->nullOnDelete();
            $table->string('consent_type')->comment('marketing, data_sharing, dependent_registration, terms');
            $table->boolean('granted')->default(true);
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('revoke_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('consent_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.consent_records');
    }
};
