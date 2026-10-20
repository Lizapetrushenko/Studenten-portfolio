<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupWorkProcess extends Model
{
    protected $fillable = ['number', 'name'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
