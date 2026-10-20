<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('lifecycle_status')->nullable()->unique();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('student_number')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('preferred_name')->nullable();
            $table->string('education')->nullable();
            $table->string('study_type')->nullable();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        Schema::create('student_reviewer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'teacher_id']);
        });

        DB::table('users')->whereNotNull('group_id')->orderBy('id')->each(function ($user) {
            DB::table('group_user')->insert([
                'group_id' => $user->group_id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $user) {
            $parts = preg_split('/\s+/', trim($user->name), 2);
            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? null,
                'last_name' => $parts[1] ?? null,
                'preferred_name' => $parts[0] ?? null,
            ]);
        }

        foreach (DB::table('portfolios')->whereNotNull('study')->get(['user_id', 'study']) as $portfolio) {
            DB::table('users')->where('id', $portfolio->user_id)->whereNull('study_type')->update(['study_type' => $portfolio->study]);
        }

        foreach (['nieuw' => 'NIEUW', 'geslaagd' => 'GESLAAGD', 'herkansen' => 'HERKANSEN', 'weg' => 'WEG'] as $status => $name) {
            DB::table('groups')->updateOrInsert(
                ['lifecycle_status' => $status],
                ['name' => $name, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $newGroupId = DB::table('groups')->where('lifecycle_status', 'nieuw')->value('id');
        foreach (DB::table('users')->where('role', 'student')->whereNull('group_id')->pluck('id') as $studentId) {
            DB::table('group_user')->insertOrIgnore([
                'group_id' => $newGroupId,
                'user_id' => $studentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('portfolios')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
        Schema::dropIfExists('student_reviewer');
        Schema::dropIfExists('group_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_number']);
            $table->dropColumn(['student_number', 'first_name', 'last_name', 'preferred_name', 'education', 'study_type']);
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique(['lifecycle_status']);
            $table->dropColumn('lifecycle_status');
        });
    }
};
