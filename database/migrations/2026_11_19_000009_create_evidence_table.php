<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evidence')) {
            Schema::create('evidence', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('work_process_id')->constrained('work_processes')->cascadeOnUpdate();
                $table->string('status', 50);
                $table->index('student_id');
                $table->index('work_process_id');
            });
        }
    }

    public function down(): void {}
};
