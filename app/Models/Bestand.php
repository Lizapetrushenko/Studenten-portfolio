<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @deprecated File metadata lives on Evidence; use Evidence directly. */
class Bestand extends Evidence
{
    protected $table = 'evidence';
    protected $fillable = ['bewijs_id','bestandsnaam','bestandspad','title','file_path','original_file_name','portfolio_id'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Bestand $file): void {
            $evidence = Evidence::find($file->bewijs_id);
            $file->portfolio_id = $evidence?->portfolio_id;
            $file->title = $file->bestandsnaam ?: 'Bestand';
            $file->file_path = $file->bestandspad;
            $file->original_file_name = $file->bestandsnaam;
            $file->status = 'idee';
            $file->offsetUnset('bestandsnaam');
            $file->offsetUnset('bestandspad');
        });
    }

    public function bewijs(): BelongsTo
    {
        return $this->belongsTo(Evidence::class, 'bewijs_id');
    }
}
