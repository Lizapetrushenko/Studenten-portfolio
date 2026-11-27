<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('education_programs')) {
            Schema::create('education_programs', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 100)->nullable()->unique();
                $table->string('name', 120)->unique();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('education_work_processes')) {
            Schema::create('education_work_processes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('education_program_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('number');
                $table->string('name', 120);
                $table->timestamps();
                $table->unique(['education_program_id', 'number']);
            });
        }
        if (! Schema::hasTable('group_work_processes')) {
            Schema::create('group_work_processes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
                $table->unsignedInteger('number');
                $table->string('name', 120);
                $table->timestamps();
                $table->unique(['group_id', 'number']);
            });
        }
        if (! Schema::hasTable('student_reviewer')) {
            Schema::create('student_reviewer', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['student_id', 'teacher_id']);
                $table->index('teacher_id');
            });
        }

        if (! Schema::hasTable('groups')) return;
        Schema::table('groups', function (Blueprint $table): void {
            if (! Schema::hasColumn('groups', 'photo_path')) $table->string('photo_path')->nullable();
            if (! Schema::hasColumn('groups', 'education_program_id')) $table->unsignedBigInteger('education_program_id')->nullable();
            if (! Schema::hasColumn('groups', 'created_at')) $table->timestamp('created_at')->nullable();
            if (! Schema::hasColumn('groups', 'updated_at')) $table->timestamp('updated_at')->nullable();
        });

        $teacherForeignKey = collect(Schema::getForeignKeys('groups'))->first(fn (array $key): bool => $key['columns'] === ['teacher_id']);
        if (Schema::hasColumn('groups', 'teacher_id') && ($teacherForeignKey['foreign_table'] ?? null) !== 'users') {
            $mappings = [];
            if (Schema::hasTable('teacher') && Schema::hasTable('users')) {
                foreach (DB::table('groups')->whereNotNull('teacher_id')->get(['id', 'teacher_id']) as $assignment) {
                    $email = DB::table('teacher')->where('id', $assignment->teacher_id)->value('email');
                    $userId = $email ? DB::table('users')->whereRaw('LOWER(email) = LOWER(?)', [$email])->where('role', 'teacher')->value('id') : null;
                    if (! $userId) throw new RuntimeException('Cannot map groups.teacher_id to a teacher role in users.');
                    $mappings[$assignment->id] = $userId;
                }
            } elseif (DB::table('groups')->whereNotNull('teacher_id')->exists()) {
                throw new RuntimeException('Cannot map existing group teachers to users.');
            }
            if ($teacherForeignKey) Schema::table('groups', fn (Blueprint $table) => $table->dropForeign(['teacher_id']));
            foreach ($mappings as $groupId => $userId) DB::table('groups')->where('id', $groupId)->update(['teacher_id' => $userId]);
            Schema::table('groups', function (Blueprint $table): void {
                $table->unsignedBigInteger('teacher_id')->nullable()->change();
                $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('groups', 'education_program_id') && ! collect(Schema::getForeignKeys('groups'))->contains(fn (array $key): bool => $key['columns'] === ['education_program_id'])) {
            Schema::table('groups', fn (Blueprint $table) => $table->foreign('education_program_id')->references('id')->on('education_programs')->nullOnDelete());
        }
    }

    public function down(): void {}
};
