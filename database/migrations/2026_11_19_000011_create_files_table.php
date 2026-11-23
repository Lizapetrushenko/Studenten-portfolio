<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('evidence_id')->constrained('evidence')->cascadeOnDelete();
                $table->string('path', 1024);
                $table->index('evidence_id');
            });
        }
    }

    public function down(): void {}
};
