<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkProcess extends Model
{
    protected $fillable = ['core_task_id', 'number', 'code', 'name'];

    protected static function booted(): void
    {
        static::creating(function (WorkProcess $process): void {
            if (! $process->core_task_id) {
                $process->core_task_id = CoreTask::query()
                    ->whereDoesntHave('workProcesses', fn ($query) => $query->where('number', $process->number))
                    ->orderBy('id')
                    ->value('id') ?? CoreTask::query()->orderBy('id')->value('id');
            }
        });
        static::saved(function (WorkProcess $process): void {
            if (Schema::hasTable('education_work_processes')) {
                $programId = $process->coreTask?->education_program_id;
                if ($programId) {
                    DB::table('education_work_processes')->updateOrInsert(
                        ['id' => $process->id],
                        ['education_program_id' => $programId, 'number' => $process->number, 'name' => $process->name, 'created_at' => now(), 'updated_at' => now()],
                    );
                }
            }
            if (! Schema::hasTable('group_work_processes')) {
                return;
            }
            $groupId = Group::whereNull('lifecycle_status')->value('id');
            if ($groupId) {
                DB::table('group_work_processes')->updateOrInsert(
                    ['id' => $process->id],
                    [
                        'group_id' => $groupId,
                        'core_task_id' => $process->core_task_id,
                        'number' => $process->number,
                        'name' => $process->name,
                        'code' => $process->code ?: ((int) $process->number <= 5
                            ? 'K1W'.$process->number
                            : 'K2W'.((int) $process->number - 5)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });
        static::deleted(function (WorkProcess $process): void {
            if (Schema::hasTable('education_work_processes')) {
                DB::table('education_work_processes')->where('id', $process->id)->delete();
            }
            if (Schema::hasTable('group_work_processes')) {
                DB::table('group_work_processes')->where('id', $process->id)->delete();
            }
        });
    }

    public function coreTask(): BelongsTo
    {
        return $this->belongsTo(CoreTask::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'number', 'id');
    }
}
