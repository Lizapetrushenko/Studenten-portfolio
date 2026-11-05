<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bewijs extends Model
{
    protected $table = 'student_evidence';
    protected $fillable = ['student_id', 'werkproces_id', 'titel', 'beschrijving', 'status', 'datum_upload'];

    protected function casts(): array
    {
        return ['datum_upload' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function werkproces(): BelongsTo
    {
        return $this->belongsTo(Werkproces::class);
    }

    public function beoordelingen(): HasMany
    {
        return $this->hasMany(Bewijsbeoordeling::class);
    }

    public function bestanden(): HasMany
    {
        return $this->hasMany(Bestand::class);
    }
}
