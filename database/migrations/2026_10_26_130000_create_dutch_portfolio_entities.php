<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beheerders', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->timestamps();
        });

        Schema::create('docenten', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->string('email')->unique();
            $table->string('opleiding')->nullable();
            $table->timestamps();
        });

        Schema::create('groepen', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->foreignId('docent_id')->constrained('docenten')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('studenten', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->string('opleiding')->nullable();
            $table->foreignId('klas_id')->nullable()->constrained('groepen')->nullOnDelete();
            $table->foreignId('beoordelaar_docent_id')->nullable()->constrained('docenten')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('groep_student', function (Blueprint $table) {
            $table->foreignId('groep_id')->constrained('groepen')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('studenten')->cascadeOnDelete();
            $table->primary(['groep_id', 'student_id']);
        });

        Schema::create('kwalificatiedossiers', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('naam');
            $table->timestamps();
        });

        Schema::create('kerntaken', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kwalificatiedossier_id')->constrained('kwalificatiedossiers')->cascadeOnDelete();
            $table->string('naam');
            $table->timestamps();
        });

        Schema::create('werkprocessen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerntaak_id')->constrained('kerntaken')->cascadeOnDelete();
            $table->string('code');
            $table->string('naam');
            $table->timestamps();
        });

        Schema::create('bewijzen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('studenten')->cascadeOnDelete();
            $table->foreignId('werkproces_id')->constrained('werkprocessen')->restrictOnDelete();
            $table->string('titel');
            $table->text('beschrijving')->nullable();
            $table->string('status')->default('ingeleverd');
            $table->timestamp('datum_upload')->nullable();
            $table->timestamps();
        });

        Schema::create('bewijsbeoordelingen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bewijs_id')->constrained('bewijzen')->cascadeOnDelete();
            $table->foreignId('docent_id')->constrained('docenten')->restrictOnDelete();
            $table->enum('beoordeling', ['akkoord', 'niet akkoord']);
            $table->text('feedback')->nullable();
            $table->timestamp('beoordelingsdatum')->nullable();
            $table->timestamps();
        });

        Schema::create('bestanden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bewijs_id')->constrained('bewijzen')->cascadeOnDelete();
            $table->string('bestandsnaam');
            $table->string('bestandspad', 2048);
            $table->timestamp('upload_datum')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bestanden');
        Schema::dropIfExists('bewijsbeoordelingen');
        Schema::dropIfExists('bewijzen');
        Schema::dropIfExists('werkprocessen');
        Schema::dropIfExists('kerntaken');
        Schema::dropIfExists('kwalificatiedossiers');
        Schema::dropIfExists('groep_student');
        Schema::dropIfExists('studenten');
        Schema::dropIfExists('groepen');
        Schema::dropIfExists('docenten');
        Schema::dropIfExists('beheerders');
    }
};
