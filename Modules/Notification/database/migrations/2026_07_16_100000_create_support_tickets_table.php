<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('public.support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('auth.users');
            $table->string('subject');
            $table->text('message');
            $table->string('status', 20)->default('open');
            $table->text('response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('auth.users');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public.support_tickets');
    }
};
