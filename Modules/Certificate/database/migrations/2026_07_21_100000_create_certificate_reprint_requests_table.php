<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate.certificate_reprint_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificate.certificates')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('auth.users');
            $table->text('reason');
            $table->string('status', 20)->default('pending')->comment('pending, approved, rejected, completed');
            $table->timestamp('requested_at');
            $table->foreignId('processed_by')->nullable()->constrained('auth.users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('certificate_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate.certificate_reprint_requests');
    }
};
