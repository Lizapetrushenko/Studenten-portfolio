<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('education_work_processes')) {
            Schema::create('education_work_processes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('education_program_id')->constrained('education_programs')->cascadeOnDelete();
                $table->unsignedInteger('number');
                $table->string('name', 120);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
    }
};
