<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evidence')) return;

        Schema::table('evidence', function (Blueprint $table): void {
            if (! Schema::hasColumn('evidence', 'portfolio_id')) $table->foreignId('portfolio_id')->nullable()->after('id')->constrained('portfolios')->cascadeOnDelete();
            if (! Schema::hasColumn('evidence', 'title')) $table->string('title')->nullable();
            if (! Schema::hasColumn('evidence', 'description')) $table->text('description')->nullable();
            if (! Schema::hasColumn('evidence', 'category')) $table->string('category')->nullable();
            if (! Schema::hasColumn('evidence', 'url')) $table->string('url')->nullable();
            if (! Schema::hasColumn('evidence', 'file_path')) $table->string('file_path')->nullable();
            if (! Schema::hasColumn('evidence', 'original_file_name')) $table->string('original_file_name')->nullable();
            if (! Schema::hasColumn('evidence', 'work_process')) $table->string('work_process')->nullable();
            if (! Schema::hasColumn('evidence', 'item_number')) $table->unsignedInteger('item_number')->nullable();
            if (! Schema::hasColumn('evidence', 'idea')) $table->text('idea')->nullable();
            if (! Schema::hasColumn('evidence', 'note')) $table->text('note')->nullable();
            if (! Schema::hasColumn('evidence', 'feedback')) $table->text('feedback')->nullable();
            if (! Schema::hasColumn('evidence', 'feedback_author_name')) $table->string('feedback_author_name')->nullable();
            if (! Schema::hasColumn('evidence', 'submitted_where')) $table->string('submitted_where')->nullable();
            if (! Schema::hasColumn('evidence', 'is_completed')) $table->boolean('is_completed')->default(false);
            if (! Schema::hasColumn('evidence', 'created_at')) $table->timestamp('created_at')->nullable();
            if (! Schema::hasColumn('evidence', 'updated_at')) $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void {}
};
