<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ev_id')->constrained('evidence')->cascadeOnDelete();
            $table->string('path');
        });

        DB::table('evidence')
            ->whereNotNull('file_path')
            ->where('file_path', '<>', '')
            ->orderBy('id')
            ->each(function ($evidence): void {
                DB::table('files')->insert([
                    'ev_id' => $evidence->id,
                    'path' => $evidence->file_path,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
