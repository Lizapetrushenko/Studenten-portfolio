<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('portfolios')) {
            Schema::create('portfolios', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->text('bio')->nullable();
                $table->string('study')->nullable();
                $table->timestamps();
                $table->index('user_id');
            });
        }
    }

    public function down(): void {}
};
