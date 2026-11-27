<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/** @deprecated Use EducationProgram; kept as a compatibility alias. */
class Kwalificatiedossier extends EducationProgram
{
    protected $table = 'education_programs';
    protected $fillable = ['code', 'naam', 'name'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Kwalificatiedossier $dossier): void {
            $dossier->name = $dossier->naam ?: $dossier->name;
            $dossier->offsetUnset('naam');
        });
    }

    public function kerntaken(): HasMany
    {
        return $this->hasMany(Kerntaak::class, 'education_program_id');
    }
}
