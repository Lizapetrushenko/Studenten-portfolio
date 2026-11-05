<?php

namespace Tests\Unit\Models;

use App\Models\Beheerder;
use App\Models\Bestand;
use App\Models\Bewijs;
use App\Models\Bewijsbeoordeling;
use App\Models\Docent;
use App\Models\Groep;
use App\Models\Kerntaak;
use App\Models\Kwalificatiedossier;
use App\Models\Student;
use App\Models\Werkproces;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyPortfolioRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_portfolio_models_resolve_their_configured_tables_and_relations(): void
    {
        $administrator = Beheerder::create(['naam' => 'Beheerder']);
        $teacher = Docent::create(['naam' => 'Docent', 'email' => 'docent@example.test']);
        $group = Groep::create(['naam' => 'Klas', 'docent_id' => $teacher->id]);
        $student = Student::create([
            'naam' => 'Student', 'klas_id' => $group->id, 'beoordelaar_docent_id' => $teacher->id,
        ]);
        $group->studenten()->attach($student->id);
        $dossier = Kwalificatiedossier::create(['code' => 'SD', 'naam' => 'Software development']);
        $coreTask = Kerntaak::create(['kwalificatiedossier_id' => $dossier->id, 'naam' => 'Ontwikkelen']);
        $workProcess = Werkproces::create(['kerntaak_id' => $coreTask->id, 'code' => 'P1', 'naam' => 'Plannen']);
        $evidence = Bewijs::create([
            'student_id' => $student->id, 'werkproces_id' => $workProcess->id, 'titel' => 'Bewijs',
        ]);
        $review = Bewijsbeoordeling::create([
            'bewijs_id' => $evidence->id, 'docent_id' => $teacher->id, 'beoordeling' => 'akkoord',
        ]);
        $file = Bestand::create([
            'bewijs_id' => $evidence->id, 'bestandsnaam' => 'bewijs.pdf', 'bestandspad' => 'evidence/bewijs.pdf',
        ]);

        $this->assertSame('administrators', $administrator->getTable());
        $this->assertSame($teacher->id, $group->docent->id);
        $this->assertTrue($group->studenten->contains('id', $student->id));
        $this->assertSame($group->id, $student->klas->id);
        $this->assertSame($teacher->id, $student->beoordelaar->id);
        $this->assertSame($coreTask->id, $workProcess->kerntaak->id);
        $this->assertSame($workProcess->id, $evidence->werkproces->id);
        $this->assertSame($student->id, $evidence->student->id);
        $this->assertSame($review->id, $evidence->beoordelingen->first()->id);
        $this->assertSame($file->id, $evidence->bestanden->first()->id);
        $this->assertSame($evidence->id, $review->bewijs->id);
        $this->assertSame($teacher->id, $review->docent->id);
        $this->assertSame($evidence->id, $file->bewijs->id);
        $this->assertSame($workProcess->id, $coreTask->werkprocessen->first()->id);
        $this->assertSame($coreTask->id, $dossier->kerntaken->first()->id);
    }
}
