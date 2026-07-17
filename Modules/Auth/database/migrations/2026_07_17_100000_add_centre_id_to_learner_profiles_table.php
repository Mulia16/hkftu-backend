<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth.learner_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('centre_id')->nullable()->after('status');
            $table->foreign('centre_id')->references('id')->on('class_scheduling.centres')->nullOnDelete();
            $table->index('centre_id');
        });
    }

    public function down(): void
    {
        Schema::table('auth.learner_profiles', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropIndex(['centre_id']);
            $table->dropColumn('centre_id');
        });
    }
};
