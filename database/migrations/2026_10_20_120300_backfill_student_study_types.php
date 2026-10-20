<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('portfolios')->whereNotNull('study')->get(['user_id', 'study']) as $portfolio) {
            DB::table('users')->where('id', $portfolio->user_id)->whereNull('study_type')->update(['study_type' => $portfolio->study]);
        }
    }

    public function down(): void
    {
        // This migration only copies existing portfolio values into the student profile.
    }
};
