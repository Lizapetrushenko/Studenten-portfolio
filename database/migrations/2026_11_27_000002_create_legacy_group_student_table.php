<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('evidence')) {
            Schema::table('evidence', function (Blueprint $table): void {
                if (! Schema::hasColumn('evidence', 'student_id')) $table->foreignId('student_id')->nullable();
                if (! Schema::hasColumn('evidence', 'work_process_id')) $table->foreignId('work_process_id')->nullable();
                if (! Schema::hasColumn('evidence', 'bewijs_id')) $table->unsignedBigInteger('bewijs_id')->nullable();
                if (! Schema::hasColumn('evidence', 'docent_id')) $table->foreignId('docent_id')->nullable();
                if (! Schema::hasColumn('evidence', 'beoordeling')) $table->string('beoordeling')->nullable();
                if (! Schema::hasColumn('evidence', 'bestandsnaam')) $table->string('bestandsnaam')->nullable();
                if (! Schema::hasColumn('evidence', 'bestandspad')) $table->string('bestandspad')->nullable();
            });
        }

        if (! Schema::hasTable('group_student')) {
            Schema::create('group_student', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
    }
};
