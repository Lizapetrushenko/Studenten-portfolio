<?php

namespace App\Models;

use App\Support\StudentDocumentCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    protected $fillable = ['title', 'description', 'category', 'url', 'file_path', 'original_file_name', 'work_process', 'item_number', 'status', 'idea', 'note', 'feedback', 'feedback_author_name', 'submitted_where', 'is_completed'];
    protected $casts = ['is_completed' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn (Evidence $evidence) => $evidence->forgetStudentPortfolioCache());
        static::deleted(fn (Evidence $evidence) => $evidence->forgetStudentPortfolioCache());
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    private function forgetStudentPortfolioCache(): void
    {
        $studentId = $this->relationLoaded('portfolio')
            ? $this->portfolio?->user_id
            : $this->portfolio()->value('user_id');

        if ($studentId) {
            StudentDocumentCache::forgetPortfolio((int) $studentId);
        }
    }
}
