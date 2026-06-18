<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth.staff_profiles', function (Blueprint $table) {
            $table->foreign('centre_id')
                ->references('id')
                ->on('class_scheduling.centres')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('auth.staff_profiles', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });
    }
};
