<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150);
                $table->foreignId('teacher_id')->constrained('teacher')->cascadeOnUpdate();
            });
        }
    }

    public function down(): void {}
};
