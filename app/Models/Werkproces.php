<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @deprecated Use WorkProcess; kept as a compatibility alias. */
class Werkproces extends WorkProcess
{
    protected $table = 'work_processes';
    protected $fillable = ['kerntaak_id', 'core_task_id', 'code', 'naam', 'name', 'number'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Werkproces $process): void {
            $process->core_task_id = $process->kerntaak_id ?: $process->core_task_id;
            $process->name = $process->naam ?: $process->name;
            $process->number = $process->number ?: 1;
            $process->offsetUnset('kerntaak_id');
            $process->offsetUnset('naam');
        });
    }

    public function kerntaak(): BelongsTo
    {
        return $this->belongsTo(Kerntaak::class, 'core_task_id');
    }
}
