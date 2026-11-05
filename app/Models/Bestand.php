<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bestand extends Model
{
    protected $table = 'student_evidence_files';
    protected $fillable = ['bewijs_id', 'bestandsnaam', 'bestandspad', 'upload_datum'];

    protected function casts(): array
    {
        return ['upload_datum' => 'datetime'];
    }

    public function bewijs(): BelongsTo
    {
        return $this->belongsTo(Bewijs::class);
    }
}
