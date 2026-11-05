<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = ['naam', 'opleiding', 'klas_id', 'beoordelaar_docent_id'];

    public function klas(): BelongsTo
    {
        return $this->belongsTo(Groep::class, 'klas_id');
    }

    public function beoordelaar(): BelongsTo
    {
        return $this->belongsTo(Docent::class, 'beoordelaar_docent_id');
    }

    public function groepen(): BelongsToMany
    {
        return $this->belongsToMany(Groep::class, 'group_students', 'student_id', 'groep_id');
    }

    public function bewijzen(): HasMany
    {
        return $this->hasMany(Bewijs::class);
    }
}
