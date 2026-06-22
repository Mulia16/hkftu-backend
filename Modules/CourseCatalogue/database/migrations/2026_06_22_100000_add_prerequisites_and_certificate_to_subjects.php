<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_catalogue.subjects', function (Blueprint $table) {
            $table->json('prerequisites')->nullable()->after('lesson_hours');
            $table->boolean('certificate_eligible')->default(true)->after('prerequisites');
        });
    }

    public function down(): void
    {
        Schema::table('course_catalogue.subjects', function (Blueprint $table) {
            $table->dropColumn(['prerequisites', 'certificate_eligible']);
        });
    }
};
