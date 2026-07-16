<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting.report_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('reporting.report_templates');
            $table->foreignId('requested_by')->constrained('auth.users');
            $table->jsonb('parameters_json')->nullable();
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting.report_runs');
    }
};
