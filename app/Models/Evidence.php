<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    protected $fillable = ['title', 'description', 'category', 'url', 'file_path', 'work_process', 'item_number', 'status', 'idea', 'note', 'submitted_where', 'is_completed'];

    protected $casts = ['is_completed' => 'boolean'];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
