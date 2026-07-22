<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth.member_pricing_config', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_percentage', 5, 2)->default(0);
            $table->json('by_type')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('auth.users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('auth.member_pricing_config')->insert([
            'default_percentage' => (float) env('MEMBER_DISCOUNT_PERCENTAGE', 10),
            'by_type' => json_encode((object) []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.member_pricing_config');
    }
};
