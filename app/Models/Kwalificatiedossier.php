<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kwalificatiedossier extends Model
{
    protected $table = 'portfolio_qualification_dossiers';
    protected $fillable = ['code', 'naam'];

    public function kerntaken(): HasMany
    {
        return $this->hasMany(Kerntaak::class);
    }
}
