<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_work_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('name');
            $table->timestamps();
            $table->unique(['group_id', 'number']);
        });

        $defaults = [
            'Afstemmen en plannen', 'Ontwerpen', 'Realisatie', 'Testen',
            'Verbetervoorstellen', 'Projectteam', 'Presenteren', 'Evalueer',
        ];

        foreach (DB::table('groups')->pluck('id') as $groupId) {
            foreach ($defaults as $index => $name) {
                DB::table('group_work_processes')->insert([
                    'group_id' => $groupId,
                    'number' => $index + 1,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_work_processes');
    }
};
