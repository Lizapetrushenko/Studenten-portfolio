<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('education_codes')) {
            Schema::create('education_codes', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 100)->unique();
                $table->string('name');
            });
        }
    }

    public function down(): void {}
};
