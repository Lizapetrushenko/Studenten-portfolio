<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('groups') && ! Schema::hasColumn('groups', 'lifecycle_status')) {
            Schema::table('groups', function (Blueprint $table): void {
                $table->string('lifecycle_status')->nullable()->unique();
            });
        }

        if (! Schema::hasTable('group_user')) {
            Schema::create('group_user', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['group_id', 'user_id']);
            });
        }
    }

    public function down(): void {}
};
