<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class EducationProgram extends Model {
    protected $fillable = ['name'];
    public function workProcesses(): HasMany { return $this->hasMany(EducationWorkProcess::class)->orderBy('number'); }
    public function groups(): HasMany { return $this->hasMany(Group::class); }
}
