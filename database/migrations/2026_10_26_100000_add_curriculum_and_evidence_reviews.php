<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualification_dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('core_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qualification_dossier_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['qualification_dossier_id', 'code']);
        });

        Schema::create('curriculum_work_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('core_task_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['core_task_id', 'code']);
        });

        Schema::table('evidence', function (Blueprint $table) {
            $table->foreignId('curriculum_work_process_id')
                ->nullable()
                ->after('portfolio_id')
                ->constrained('curriculum_work_processes')
                ->nullOnDelete();
        });

        Schema::create('evidence_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->text('feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_reviews');

        Schema::table('evidence', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curriculum_work_process_id');
        });

        Schema::dropIfExists('curriculum_work_processes');
        Schema::dropIfExists('core_tasks');
        Schema::dropIfExists('qualification_dossiers');
    }
};
