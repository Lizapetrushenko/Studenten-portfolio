<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evidence')) return;
        Schema::table('evidence', function (Blueprint $table): void {
            if (Schema::hasColumn('evidence', 'student_id')) $table->unsignedBigInteger('student_id')->nullable()->change();
            if (Schema::hasColumn('evidence', 'work_process_id')) $table->unsignedBigInteger('work_process_id')->nullable()->change();
        });
    }

    public function down(): void {}
};
