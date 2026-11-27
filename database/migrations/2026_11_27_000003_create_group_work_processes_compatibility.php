<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('group_work_processes')) {
            Schema::create('group_work_processes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
                $table->foreignId('core_task_id')->nullable();
                $table->unsignedInteger('number');
                $table->string('name', 120);
                $table->string('code', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
    }
};
