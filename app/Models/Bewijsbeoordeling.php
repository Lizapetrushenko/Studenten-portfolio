<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bewijsbeoordeling extends Model
{
    protected $table = 'student_evidence_reviews';
    protected $fillable = ['bewijs_id', 'docent_id', 'beoordeling', 'feedback', 'beoordelingsdatum'];

    protected function casts(): array
    {
        return ['beoordelingsdatum' => 'datetime'];
    }

    public function bewijs(): BelongsTo
    {
        return $this->belongsTo(Bewijs::class);
    }

    public function docent(): BelongsTo
    {
        return $this->belongsTo(Docent::class);
    }
}
