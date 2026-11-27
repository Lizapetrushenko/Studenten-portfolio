<?php

namespace App\Models;

use App\Support\StudentDocumentCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Group extends Model
{
    protected $fillable = ['name', 'teacher_id', 'photo_path', 'education_program_id'];

    protected static function booted(): void
    {
        static::saved(fn () => StudentDocumentCache::forgetStudentDirectory());
        static::deleted(fn () => StudentDocumentCache::forgetStudentDirectory());
        static::creating(function (Group $group): void {
            if (! $group->education_program_id) {
                $group->education_program_id = EducationProgram::where('code', '25998')->value('id');
            }
        });

        static::deleting(function (Group $group) {
            if ($group->lifecycle_status !== null) {
                throw ValidationException::withMessages(['group' => 'Vaste statusgroepen kunnen niet worden verwijderd.']);
            }
        });
    }

    public function educationProgram(): BelongsTo { return $this->belongsTo(EducationProgram::class); }

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function students(): BelongsToMany { return $this->belongsToMany(User::class)->where('users.role', '!=', 'test_account')->withTimestamps(); }
    public function workProcesses(): HasMany
    {
        return $this->hasMany(GroupWorkProcess::class, 'group_id')->orderBy('number');
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
