<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolment.transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_enrolment_id')->constrained('enrolment.enrolments');
            $table->foreignId('new_class_id')->constrained('class_scheduling.classes');
            $table->foreignId('new_enrolment_id')->nullable()->constrained('enrolment.enrolments');
            $table->decimal('price_difference', 10, 2)->default(0);
            $table->string('status', 20)->default('requested');
            $table->text('reason')->nullable();
            $table->foreignId('requested_by')->constrained('auth.users');
            $table->foreignId('approved_by')->nullable()->constrained('auth.users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment.transfers');
    }
};
