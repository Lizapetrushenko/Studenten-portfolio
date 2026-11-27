<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/** @deprecated Use CoreTask; kept as a compatibility alias. */
class Kerntaak extends CoreTask
{
    protected $table = 'core_tasks';
    protected $fillable = ['kwalificatiedossier_id', 'education_program_id', 'task_number', 'naam', 'name'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Kerntaak $task): void {
            $task->education_program_id = $task->kwalificatiedossier_id ?: $task->education_program_id;
            $task->name = $task->naam ?: $task->name;
            $task->task_number = $task->task_number ?: 'LEGACY';
            $task->offsetUnset('kwalificatiedossier_id');
            $task->offsetUnset('naam');
        });
    }

    public function werkprocessen(): HasMany
    {
        return $this->hasMany(Werkproces::class, 'core_task_id');
    }
}
