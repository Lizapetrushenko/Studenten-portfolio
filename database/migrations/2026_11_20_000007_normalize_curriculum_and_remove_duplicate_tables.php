<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['teacher', 'students', 'group_student', 'evidence_reviews', 'files', 'education_work_processes', 'group_work_processes'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new RuntimeException("Cannot remove legacy table {$table}: it still contains records.");
            }
        }

        if (Schema::hasTable('admin')) {
            foreach (DB::table('admin')->get() as $legacyAdmin) {
                $user = DB::table('users')->whereRaw('LOWER(email) = LOWER(?)', [$legacyAdmin->email])->first();
                if (! $user) throw new RuntimeException('Cannot safely map a legacy admin account to users.');
                DB::table('users')->where('id', $user->id)->update(['role' => 'admin', 'is_admin' => true]);
            }
        }

        if (! Schema::hasColumn('education_programs', 'code')) {
            Schema::table('education_programs', fn (Blueprint $table) => $table->string('code', 100)->nullable()->unique());
        }

        if (Schema::hasTable('education_codes')) {
            foreach (DB::table('education_codes')->orderBy('id')->get() as $legacyProgram) {
                $program = DB::table('education_programs')->where('code', $legacyProgram->code)->first();
                if ($program) continue;
                $name = $legacyProgram->code === '25998' ? 'Software Developer' : $legacyProgram->name;
                $program = DB::table('education_programs')->where('name', $name)->first();
                if ($program) DB::table('education_programs')->where('id', $program->id)->update(['code' => $legacyProgram->code]);
                else DB::table('education_programs')->insert(['code' => $legacyProgram->code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        if (Schema::hasTable('core_tasks') && Schema::hasColumn('core_tasks', 'kd_id')) {
            Schema::table('core_tasks', function (Blueprint $table): void {
                if (! Schema::hasColumn('core_tasks', 'education_program_id')) $table->unsignedBigInteger('education_program_id')->nullable()->after('id');
                if (! Schema::hasColumn('core_tasks', 'name')) $table->string('name', 255)->nullable()->after('task_number');
                if (! Schema::hasColumn('core_tasks', 'created_at')) $table->timestamps();
            });
            foreach (DB::table('core_tasks')->get(['id', 'kd_id', 'task_number']) as $task) {
                $code = DB::table('education_codes')->where('id', $task->kd_id)->value('code');
                $programId = $code ? DB::table('education_programs')->where('code', $code)->value('id') : null;
                if (! $programId) throw new RuntimeException("Cannot map core task {$task->id} to an education program.");
                DB::table('core_tasks')->where('id', $task->id)->update(['education_program_id' => $programId, 'name' => $task->task_number]);
            }
            $foreign = collect(Schema::getForeignKeys('core_tasks'))->first(fn (array $key): bool => $key['columns'] === ['kd_id']);
            if ($foreign) Schema::table('core_tasks', fn (Blueprint $table) => $table->dropForeign(['kd_id']));
            Schema::table('core_tasks', function (Blueprint $table): void {
                $table->unsignedBigInteger('education_program_id')->nullable(false)->change();
                $table->foreign('education_program_id')->references('id')->on('education_programs')->cascadeOnDelete();
                if (Schema::hasIndex('core_tasks', ['kd_id'])) $table->dropIndex(['kd_id']);
                $table->dropColumn('kd_id');
            });
        }

        if (Schema::hasTable('work_processes')) {
            if (! Schema::hasColumn('work_processes', 'number')) Schema::table('work_processes', fn (Blueprint $table) => $table->unsignedInteger('number')->nullable());
            if (! Schema::hasColumn('work_processes', 'created_at')) Schema::table('work_processes', fn (Blueprint $table) => $table->timestamps());
            Schema::table('work_processes', fn (Blueprint $table) => $table->string('code', 100)->nullable()->change());
            foreach (DB::table('work_processes')->whereNull('number')->get(['id', 'code']) as $process) {
                if (preg_match('/-K([12])-W(\d+)$/', (string) $process->code, $match)) {
                    DB::table('work_processes')->where('id', $process->id)->update(['number' => ($match[1] === '1' ? 0 : 5) + (int) $match[2]]);
                }
            }
            if (DB::table('work_processes')->whereNull('number')->exists()) throw new RuntimeException('Cannot assign curriculum numbers to every existing work process.');
            Schema::table('work_processes', function (Blueprint $table): void {
                $table->unsignedInteger('number')->nullable(false)->change();
                if (! Schema::hasIndex('work_processes', ['core_task_id', 'number'])) $table->unique(['core_task_id', 'number']);
                if (! Schema::hasIndex('work_processes', ['code'], 'unique')) $table->unique('code');
            });
        }

        $programId = DB::table('education_programs')->where('code', '25998')->value('id');
        if (! $programId) {
            $programId = DB::table('education_programs')->where('name', 'Software Developer')->value('id');
            if ($programId) DB::table('education_programs')->where('id', $programId)->update(['code' => '25998']);
            else $programId = DB::table('education_programs')->insertGetId(['code' => '25998', 'name' => 'Software Developer', 'created_at' => now(), 'updated_at' => now()]);
        }

        $catalogue = [
            ['task_number' => 'B1-K1', 'name' => 'Realiseert software', 'processes' => [
                ['code' => 'B1-K1-W1', 'name' => 'Stemt opdracht af, plant werkzaamheden en bewaakt de voortgang'],
                ['code' => 'B1-K1-W2', 'name' => 'Maakt een technisch ontwerp voor software'],
                ['code' => 'B1-K1-W3', 'name' => 'Realiseert (onderdelen van) software'],
                ['code' => 'B1-K1-W4', 'name' => 'Test software'],
                ['code' => 'B1-K1-W5', 'name' => 'Doet verbetervoorstellen voor de software'],
            ]],
            ['task_number' => 'B1-K2', 'name' => 'Voert ICT-projecten uit', 'processes' => [
                ['code' => 'B1-K2-W1', 'name' => 'Werkt samen in een projectteam'],
                ['code' => 'B1-K2-W2', 'name' => 'Presenteert het opgeleverde werk'],
                ['code' => 'B1-K2-W3', 'name' => 'Evalueert de samenwerking'],
            ]],
        ];
        $number = 1;
        foreach ($catalogue as $taskData) {
            $processes = $taskData['processes'];
            unset($taskData['processes']);
            $taskId = DB::table('core_tasks')->where('education_program_id', $programId)->where('task_number', $taskData['task_number'])->value('id');
            if ($taskId) DB::table('core_tasks')->where('id', $taskId)->update(['name' => $taskData['name'], 'updated_at' => now()]);
            else $taskId = DB::table('core_tasks')->insertGetId(['education_program_id' => $programId, ...$taskData, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($processes as $process) {
                DB::table('work_processes')->updateOrInsert(['code' => $process['code']], ['core_task_id' => $taskId, 'number' => $number++, ...$process, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        if (Schema::hasTable('evidence')) {
            foreach (['student_id', 'work_process_id'] as $column) {
                if (! Schema::hasColumn('evidence', $column)) continue;
                if (DB::table('evidence')->whereNotNull($column)->exists()) throw new RuntimeException("Cannot remove evidence.{$column}: legacy links are still populated.");
                $foreign = collect(Schema::getForeignKeys('evidence'))->first(fn (array $key): bool => $key['columns'] === [$column]);
                $index = collect(Schema::getIndexes('evidence'))->first(fn (array $key): bool => $key['columns'] === [$column] && ! ($key['primary'] ?? false));
                Schema::table('evidence', function (Blueprint $table) use ($column, $foreign, $index): void {
                    if ($foreign) $table->dropForeign([$column]);
                    if ($index) $table->dropIndex($index['name']);
                    $table->dropColumn($column);
                });
            }
        }

        foreach (['files', 'evidence_reviews', 'group_student', 'students', 'teacher', 'admin', 'education_work_processes', 'group_work_processes', 'education_codes'] as $table) Schema::dropIfExists($table);
    }

    public function down(): void
    {
        throw new RuntimeException('This cleanup migration is intentionally irreversible.');
    }
};
