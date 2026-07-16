<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth.users', function (Blueprint $table) {
            $table->string('mfa_secret')->nullable()->after('mfa_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('auth.users', function (Blueprint $table) {
            $table->dropColumn('mfa_secret');
        });
    }
};
