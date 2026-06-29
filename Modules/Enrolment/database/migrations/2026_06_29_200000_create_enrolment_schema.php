<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createIfNotExists('enrolment');
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment');
    }
};
