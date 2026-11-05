<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kerntaak extends Model
{
    protected $table = 'portfolio_core_tasks';
    protected $fillable = ['kwalificatiedossier_id', 'naam'];

    public function kwalificatiedossier(): BelongsTo
    {
        return $this->belongsTo(Kwalificatiedossier::class);
    }

    public function werkprocessen(): HasMany
    {
        return $this->hasMany(Werkproces::class);
    }
}
