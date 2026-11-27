<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @deprecated Reviews are stored on Evidence; use Evidence directly. */
class Bewijsbeoordeling extends Evidence
{
    protected $table = 'evidence';
    protected $fillable = ['bewijs_id','docent_id','beoordeling','title','portfolio_id','description','status'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Bewijsbeoordeling $review): void {
            $evidence = Evidence::find($review->bewijs_id);
            $review->portfolio_id = $evidence?->portfolio_id;
            $review->title = 'Beoordeling';
            $review->description = 'review-'.$review->bewijs_id;
            $review->status = $review->beoordeling === 'akkoord' ? 'akkoord' : 'niet akkoord';
            $review->bewijs_id = $review->getAttribute('bewijs_id');
            $review->docent_id = $review->docent_id;
            $teacher = User::find($review->docent_id);
            $review->feedback_author_name = $teacher?->name;
        });
    }

    public function bewijs(): BelongsTo
    {
        return $this->belongsTo(Evidence::class, 'bewijs_id');
    }

    public function docent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docent_id');
    }
}
