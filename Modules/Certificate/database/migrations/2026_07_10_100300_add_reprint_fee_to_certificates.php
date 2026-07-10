<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate.certificates', function (Blueprint $table) {
            $table->decimal('reprint_fee', 10, 2)->nullable()->after('reprint_reason');
            $table->text('learner_reprint_reason')->nullable()->after('reprint_fee');
        });
    }

    public function down(): void
    {
        Schema::table('certificate.certificates', function (Blueprint $table) {
            $table->dropColumn(['reprint_fee', 'learner_reprint_reason']);
        });
    }
};
