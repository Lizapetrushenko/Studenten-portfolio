<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150);
                $table->string('email')->unique();
                $table->string('password');
            });
        }
    }

    public function down(): void {}
};
