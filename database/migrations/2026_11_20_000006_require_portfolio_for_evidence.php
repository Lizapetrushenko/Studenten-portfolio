<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evidence') || ! Schema::hasColumn('evidence', 'portfolio_id')) return;
        if (DB::table('evidence')->whereNull('portfolio_id')->exists()) {
            throw new RuntimeException('Evidence rows without a portfolio must be linked before requiring portfolio_id.');
        }
        $foreign = collect(Schema::getForeignKeys('evidence'))->first(fn (array $key): bool => $key['columns'] === ['portfolio_id']);
        if ($foreign) Schema::table('evidence', fn (Blueprint $table) => $table->dropForeign(['portfolio_id']));
        Schema::table('evidence', function (Blueprint $table): void {
            $table->unsignedBigInteger('portfolio_id')->nullable(false)->change();
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->cascadeOnDelete();
        });
    }

    public function down(): void {}
};
