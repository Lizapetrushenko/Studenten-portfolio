<?php

namespace App\Models;

use App\Support\StudentDocumentCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    protected $fillable = ['user_id','title', 'bio', 'study'];

    protected static function booted(): void
    {
        static::saved(fn (Portfolio $portfolio) => StudentDocumentCache::forgetPortfolio((int) $portfolio->user_id));
        static::deleted(fn (Portfolio $portfolio) => StudentDocumentCache::forgetPortfolio((int) $portfolio->user_id));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

}
