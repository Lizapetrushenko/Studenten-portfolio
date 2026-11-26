<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) return;

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'username')) $table->string('username')->nullable()->unique();
            if (! Schema::hasColumn('users', 'role')) $table->string('role')->default('student');
            if (! Schema::hasColumn('users', 'is_admin')) $table->boolean('is_admin')->default(false);
            if (! Schema::hasColumn('users', 'first_name')) $table->string('first_name', 120)->nullable();
            if (! Schema::hasColumn('users', 'last_name')) $table->string('last_name', 120)->nullable();
            if (! Schema::hasColumn('users', 'preferred_name')) $table->string('preferred_name', 120)->nullable();
            if (! Schema::hasColumn('users', 'student_number')) $table->string('student_number', 40)->nullable()->unique();
            if (! Schema::hasColumn('users', 'leerweg')) $table->string('leerweg', 20)->nullable();
            if (! Schema::hasColumn('users', 'education')) $table->string('education')->nullable();
            if (! Schema::hasColumn('users', 'study_type')) $table->string('study_type')->nullable();
            if (! Schema::hasColumn('users', 'mfa_secret')) $table->text('mfa_secret')->nullable();
            if (! Schema::hasColumn('users', 'mfa_enabled')) $table->boolean('mfa_enabled')->default(false);
            if (! Schema::hasColumn('users', 'group_id')) $table->unsignedBigInteger('group_id')->nullable();
        });

        if (Schema::hasTable('groups') && Schema::hasColumn('users', 'group_id')
            && ! collect(Schema::getForeignKeys('users'))->contains(fn (array $key): bool => $key['columns'] === ['group_id'])) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            });
        }
    }

    public function down(): void {}
};
