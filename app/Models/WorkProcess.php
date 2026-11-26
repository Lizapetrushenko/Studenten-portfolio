<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkProcess extends Model
{
    protected $fillable = ['core_task_id', 'number', 'code', 'name'];

    public function coreTask(): BelongsTo
    {
        return $this->belongsTo(CoreTask::class);
    }
}
