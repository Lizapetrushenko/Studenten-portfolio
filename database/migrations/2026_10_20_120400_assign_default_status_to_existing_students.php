<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $newGroupId = DB::table('groups')->where('lifecycle_status', 'nieuw')->value('id');

        foreach (DB::table('users')->where('role', 'student')->pluck('id') as $studentId) {
            $hasStatus = DB::table('group_user')
                ->join('groups', 'groups.id', '=', 'group_user.group_id')
                ->where('group_user.user_id', $studentId)
                ->whereNotNull('groups.lifecycle_status')
                ->exists();

            if (!$hasStatus) {
                DB::table('group_user')->insertOrIgnore([
                    'group_id' => $newGroupId,
                    'user_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Existing memberships are retained to avoid deleting intentional student group assignments.
    }
};
