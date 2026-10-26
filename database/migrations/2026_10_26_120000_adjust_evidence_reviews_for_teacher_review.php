<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_reviews', function (Blueprint $table) {
            $table->renameColumn('reviewer_id', 'docent_id');
            $table->renameColumn('feedback', 'docent_notitie');
        });

        Schema::table('evidence_reviews', function (Blueprint $table) {
            $table->enum('status', ['akkoord', 'niet akkoord'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('evidence_reviews', function (Blueprint $table) {
            $table->string('status')->change();
        });

        Schema::table('evidence_reviews', function (Blueprint $table) {
            $table->renameColumn('docent_id', 'reviewer_id');
            $table->renameColumn('docent_notitie', 'feedback');
        });
    }
};
