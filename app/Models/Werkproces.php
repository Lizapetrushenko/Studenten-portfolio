<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Werkproces extends Model
{
    protected $table = 'qualification_work_processes';
    protected $fillable = ['kerntaak_id', 'code', 'naam'];

    public function kerntaak(): BelongsTo
    {
        return $this->belongsTo(Kerntaak::class);
    }

    public function bewijzen(): HasMany
    {
        return $this->hasMany(Bewijs::class);
    }
}
