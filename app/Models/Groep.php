<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groep extends Model
{
    protected $table = 'teaching_groups';
    protected $fillable = ['naam', 'docent_id'];

    public function docent(): BelongsTo
    {
        return $this->belongsTo(Docent::class);
    }

    public function studenten(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_students', 'groep_id', 'student_id');
    }

    public function studentenMetDezeKlas(): HasMany
    {
        return $this->hasMany(Student::class, 'klas_id');
    }
}
