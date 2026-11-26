<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evidence_reviews')) {
            Schema::create('evidence_reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('evidence_id')->constrained('evidence')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('teacher')->cascadeOnUpdate();
                $table->string('status', 50);
                $table->text('note')->nullable();
                $table->index('evidence_id');
                $table->index('teacher_id');
            });
        }
    }

    public function down(): void {}
};
