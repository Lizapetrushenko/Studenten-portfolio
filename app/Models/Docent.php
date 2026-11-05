<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docent extends Model
{
    protected $table = 'teachers';
    protected $fillable = ['naam', 'email', 'opleiding'];

    public function groepen(): HasMany
    {
        return $this->hasMany(Groep::class);
    }

    public function beoordeeldeStudenten(): HasMany
    {
        return $this->hasMany(Student::class, 'beoordelaar_docent_id');
    }

    public function bewijsbeoordelingen(): HasMany
    {
        return $this->hasMany(Bewijsbeoordeling::class);
    }
}
