<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting.report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('format')->default('pdf');
            $table->string('query_key');
            $table->jsonb('parameters_json')->nullable();
            $table->timestamps();

            $table->index('query_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting.report_templates');
    }
};
