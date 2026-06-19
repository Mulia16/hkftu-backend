<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_scheduling.clash_check_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes')->cascadeOnDelete();
            $table->enum('severity', ['warning', 'error']);
            $table->string('check_type', 50);
            $table->string('message');
            $table->foreignId('resolved_by')->nullable()->constrained('auth.users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_scheduling.clash_check_results');
    }
};
