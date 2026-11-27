<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @deprecated Use Group; kept as a compatibility alias. */
class Groep extends Group
{
    protected $table = 'groups';

    protected $fillable = ['naam', 'name', 'docent_id', 'teacher_id', 'education_program_id'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Groep $group): void {
            $group->name = $group->naam ?: $group->name;
            $group->teacher_id = $group->docent_id ?: $group->teacher_id;
            $group->offsetUnset('docent_id');
            $group->offsetUnset('naam');
        });
    }

    public function studenten(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')->withTimestamps();
    }

    public function docent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
