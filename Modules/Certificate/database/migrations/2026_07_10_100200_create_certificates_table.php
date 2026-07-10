<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate.certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_no')->unique();
            $table->foreignId('enrolment_id')->constrained('enrolment.enrolments');
            $table->foreignId('template_id')->constrained('certificate.certificate_templates');
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('auth.users');
            $table->string('pdf_file_path')->nullable();
            $table->string('status', 20)->default('issued');
            $table->text('reprint_reason')->nullable();
            $table->foreignId('reprinted_by')->nullable()->constrained('auth.users');
            $table->timestamp('reprinted_at')->nullable();
            $table->timestamps();

            $table->index('certificate_no');
            $table->index('status');
            $table->index('enrolment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate.certificates');
    }
};
