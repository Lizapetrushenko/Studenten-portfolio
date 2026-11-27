<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @deprecated Use Evidence; kept as a compatibility alias. */
class Bewijs extends Evidence
{
    protected $table = 'evidence';
    protected $fillable = ['student_id','werkproces_id','titel','title','portfolio_id','work_process','status','description'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Bewijs $evidence): void {
            $studentId = $evidence->student_id;
            $portfolio = Portfolio::firstOrCreate(['user_id' => $studentId], ['title' => 'Portfolio', 'study' => 'BOL']);
            $workProcess = WorkProcess::find($evidence->werkproces_id);
            $evidence->portfolio_id = $portfolio->id;
            $evidence->student_id = $studentId;
            $evidence->work_process_id = $workProcess?->id;
            $evidence->title = $evidence->titel ?: $evidence->title;
            $evidence->work_process = $workProcess ? (string) $workProcess->number : '1';
            $evidence->status = $evidence->status ?: 'idee';
            $evidence->offsetUnset('werkproces_id');
            $evidence->offsetUnset('titel');
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function werkproces(): BelongsTo
    {
        return $this->belongsTo(WorkProcess::class, 'work_process_id');
    }

    public function beoordelingen(): HasMany
    {
        return $this->hasMany(Bewijsbeoordeling::class, 'bewijs_id', 'id');
    }

    public function bestanden(): HasMany
    {
        return $this->hasMany(Bestand::class, 'bewijs_id', 'id')->whereNotNull('file_path');
    }
}
