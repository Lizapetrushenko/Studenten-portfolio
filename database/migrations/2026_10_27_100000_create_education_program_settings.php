<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('education_programs', function (Blueprint $table) {
            $table->id(); $table->string('name', 120)->unique(); $table->timestamps();
        });
        Schema::create('education_work_processes', function (Blueprint $table) {
            $table->id(); $table->foreignId('education_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number'); $table->string('name', 120); $table->timestamps();
            $table->unique(['education_program_id', 'number']);
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('education_program_id')->nullable()->constrained()->restrictOnDelete();
        });
        $id = DB::table('education_programs')->insertGetId(['name' => 'Software Developer', 'created_at' => now(), 'updated_at' => now()]);
        foreach (['Afstemmen en plannen', 'Ontwerpen', 'Realisatie', 'Testen', 'Verbetervoorstellen', 'Projectteam', 'Presenteren', 'Evalueer'] as $i => $name) {
            DB::table('education_work_processes')->insert(['education_program_id' => $id, 'number' => $i + 1, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('groups')->whereNull('lifecycle_status')->update(['education_program_id' => $id]);
    }
    public function down(): void {
        Schema::table('groups', fn (Blueprint $table) => $table->dropConstrainedForeignId('education_program_id'));
        Schema::dropIfExists('education_work_processes'); Schema::dropIfExists('education_programs');
    }
};
