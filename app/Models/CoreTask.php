<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoreTask extends Model
{
    protected $fillable = ['education_program_id', 'task_number', 'name'];

    public function educationProgram(): BelongsTo
    {
        return $this->belongsTo(EducationProgram::class);
    }

    public function workProcesses(): HasMany
    {
        return $this->hasMany(WorkProcess::class);
    }
}
