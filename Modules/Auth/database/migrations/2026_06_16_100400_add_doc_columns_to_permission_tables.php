<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth.roles', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('scope_type')->nullable()->after('code');
        });

        Schema::table('auth.permissions', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('module')->nullable()->after('code');
            $table->string('action')->nullable()->after('module');
        });
    }

    public function down(): void
    {
        Schema::table('auth.roles', function (Blueprint $table) {
            $table->dropColumn(['code', 'scope_type']);
        });

        Schema::table('auth.permissions', function (Blueprint $table) {
            $table->dropColumn(['code', 'module', 'action']);
        });
    }
};
