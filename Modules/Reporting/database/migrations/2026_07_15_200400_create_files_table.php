<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting.files', function (Blueprint $table) {
            $table->id();
            $table->string('storage_key')->unique();
            $table->string('filename');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('checksum')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting.files');
    }
};
