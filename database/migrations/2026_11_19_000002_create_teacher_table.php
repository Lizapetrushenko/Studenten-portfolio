<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teacher')) {
            Schema::create('teacher', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name', 150);
                $table->string('last_name', 150);
                $table->string('email')->unique();
                $table->string('password');
            });
        }
    }

    public function down(): void {}
};
