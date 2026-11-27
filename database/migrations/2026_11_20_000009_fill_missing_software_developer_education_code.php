<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('education_programs') || ! Schema::hasColumn('education_programs', 'code')) return;

        $standard = DB::table('education_programs')->where('name', 'Software Developer')->first();
        if (! $standard || $standard->code !== null || DB::table('education_programs')->where('code', '25998')->exists()) return;

        DB::table('education_programs')->where('id', $standard->id)->update(['code' => '25998', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Keep repaired catalogue data when rolling back schema changes.
    }
};
