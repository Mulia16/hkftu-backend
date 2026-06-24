<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.dependent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_user_id')->constrained('auth.users')->cascadeOnDelete();
            $table->foreignId('learner_profile_id')->constrained('auth.learner_profiles')->cascadeOnDelete();
            $table->string('relationship')->default('parent')->comment('parent, guardian, other');
            $table->timestamp('consent_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['guardian_user_id', 'learner_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.dependent_profiles');
    }
};
