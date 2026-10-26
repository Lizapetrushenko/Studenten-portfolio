<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tableNames = [
        'beheerders' => 'administrators',
        'docenten' => 'teachers',
        'groepen' => 'teaching_groups',
        'studenten' => 'students',
        'groep_student' => 'group_students',
        'kwalificatiedossiers' => 'portfolio_qualification_dossiers',
        'kerntaken' => 'portfolio_core_tasks',
        'werkprocessen' => 'qualification_work_processes',
        'bewijzen' => 'student_evidence',
        'bewijsbeoordelingen' => 'student_evidence_reviews',
        'bestanden' => 'student_evidence_files',
    ];

    public function up(): void
    {
        foreach ($this->tableNames as $oldName => $newName) {
            if (Schema::hasTable($oldName) && ! Schema::hasTable($newName)) {
                Schema::rename($oldName, $newName);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tableNames, true) as $oldName => $newName) {
            if (Schema::hasTable($newName) && ! Schema::hasTable($oldName)) {
                Schema::rename($newName, $oldName);
            }
        }
    }
};
