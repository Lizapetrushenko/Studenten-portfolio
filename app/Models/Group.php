<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class Group extends Model
{
    protected $fillable = ['name', 'teacher_id', 'photo_path', 'education_program_id'];

    protected static function booted(): void
    {
        static::deleting(function (Group $group) {
            if ($group->lifecycle_status !== null) {
                throw ValidationException::withMessages(['group' => 'Vaste statusgroepen kunnen niet worden verwijderd.']);
            }
        });
    }

    public function educationProgram(): BelongsTo { return $this->belongsTo(EducationProgram::class); }

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function students(): BelongsToMany { return $this->belongsToMany(User::class)->withTimestamps(); }
    public function workProcesses(): HasManyThrough
    {
        return $this->hasManyThrough(
            WorkProcess::class,
            CoreTask::class,
            'education_program_id',
            'core_task_id',
            'education_program_id',
            'id',
        )->orderBy('number');
    }

    public function isLifecycle(string $status): bool
    {
        return $this->lifecycle_status === $status;
    }

    public function isTeacherLinked(): bool
    {
        return $this->teacher_id !== null && mb_strtoupper(trim((string) $this->name)) !== 'NIEUW';
    }

    public function scopeTeacherLinked(Builder $query): Builder
    {
        return $query->whereNotNull('teacher_id')->whereRaw('UPPER(TRIM(name)) <> ?', ['NIEUW']);
    }
}
