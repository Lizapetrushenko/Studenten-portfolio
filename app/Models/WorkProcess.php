<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkProcess extends Model
{
    protected $fillable = ['number', 'name'];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class, 'work_process', 'number');
    }
}
