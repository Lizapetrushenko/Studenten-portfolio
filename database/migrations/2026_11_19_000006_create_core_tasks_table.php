<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('core_tasks')) {
            Schema::create('core_tasks', function (Blueprint $table): void {
                $table->id();
                $table->string('task_number', 50);
                $table->foreignId('kd_id')->constrained('education_codes')->cascadeOnUpdate();
                $table->index('kd_id');
            });
        }
    }

    public function down(): void {}
};
