<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('student_number', 50)->nullable()->unique();
                $table->string('first_name', 150);
                $table->string('last_name', 150);
                $table->string('preferred_name', 150)->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('education_type', 150)->nullable();
            });
        }
    }

    public function down(): void {}
};
