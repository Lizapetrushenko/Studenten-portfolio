<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evidence', function (Blueprint $table) {
            $table->string('work_process')->nullable()->after('portfolio_id');
            $table->unsignedTinyInteger('item_number')->nullable()->after('work_process');
            $table->string('status')->default('niet bekeken')->after('category');
            $table->text('idea')->nullable()->after('description');
            $table->text('note')->nullable()->after('idea');
            $table->boolean('is_completed')->default(false)->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidence', function (Blueprint $table) {
            $table->dropColumn(['work_process', 'item_number', 'status', 'idea', 'note', 'is_completed']);
        });
    }
};
