<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('core_tasks')) return;
        Schema::table('core_tasks', function (Blueprint $table): void {
            if (! Schema::hasIndex('core_tasks', ['education_program_id', 'task_number'], 'unique')) $table->unique(['education_program_id', 'task_number']);
        });
        Schema::table('core_tasks', fn (Blueprint $table) => $table->string('name', 255)->nullable(false)->change());
    }

    public function down(): void {}
};
