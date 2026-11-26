<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('groups', 'education_program_id')) {
            Schema::table('groups', function (Blueprint $table): void {
                $table->foreignId('education_program_id')
                    ->nullable()
                    ->after('teacher_id')
                    ->constrained('education_programs')
                    ->nullOnDelete();
            });
        }

        // Existing SD groups belong to the Software Developer curriculum.
        $softwareDeveloperId = DB::table('education_programs')->where('code', '25998')->value('id');
        if ($softwareDeveloperId) {
            DB::table('groups')
                ->whereNull('lifecycle_status')
                ->whereNull('education_program_id')
                ->where('name', 'like', 'SD%')
                ->update(['education_program_id' => $softwareDeveloperId]);
        }

        // For other groups, use the single curriculum shared by their students when it is clear.
        $groups = DB::table('groups')->whereNull('lifecycle_status')->whereNull('education_program_id')->get(['id']);
        foreach ($groups as $group) {
            $educationNames = DB::table('group_user')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->where('group_user.group_id', $group->id)
                ->where('users.role', 'student')
                ->whereNotNull('users.education')
                ->distinct()
                ->pluck('users.education');

            if ($educationNames->count() !== 1) {
                continue;
            }

            $programId = DB::table('education_programs')->where('name', $educationNames->first())->value('id');
            if ($programId) {
                DB::table('groups')->where('id', $group->id)->update(['education_program_id' => $programId]);
            }
        }
    }

    public function down(): void
    {
        // Keep group curriculum assignments when rolling back unrelated schema changes.
    }
};
