<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = ['name', 'teacher_id'];

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function students(): BelongsToMany { return $this->belongsToMany(User::class)->withTimestamps(); }
    public function workProcesses(): HasMany { return $this->hasMany(GroupWorkProcess::class)->orderBy('number'); }

    public function isLifecycle(string $status): bool
    {
        return $this->lifecycle_status === $status;
    }
}
