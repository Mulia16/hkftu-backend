<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE auth.learner_profiles ALTER COLUMN user_id DROP NOT NULL');

        DB::statement('ALTER TABLE auth.learner_profiles DROP CONSTRAINT IF EXISTS learner_profiles_user_id_unique');
        DB::statement('DROP INDEX IF EXISTS auth.learner_profiles_user_id_unique');
        DB::statement('CREATE UNIQUE INDEX learner_profiles_user_id_unique ON auth.learner_profiles (user_id) WHERE user_id IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS auth.learner_profiles_user_id_unique');

        DB::statement('ALTER TABLE auth.learner_profiles ALTER COLUMN user_id SET NOT NULL');
        DB::statement('ALTER TABLE auth.learner_profiles ADD CONSTRAINT learner_profiles_user_id_unique UNIQUE (user_id)');
    }
};
