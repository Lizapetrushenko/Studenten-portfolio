<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('work_processes')) {
            Schema::create('work_processes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('core_task_id')->constrained('core_tasks')->cascadeOnUpdate();
                $table->string('code', 100);
                $table->string('name');
                $table->index('core_task_id');
            });
        }
    }

    public function down(): void {}
};
