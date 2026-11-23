<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
class EducationProgram extends Model {
    protected $fillable = ['code', 'name'];
    public function coreTasks(): HasMany { return $this->hasMany(CoreTask::class)->orderBy('task_number'); }
    public function workProcesses(): HasManyThrough { return $this->hasManyThrough(WorkProcess::class, CoreTask::class)->orderBy('number'); }
    public function groups(): HasMany { return $this->hasMany(Group::class); }
}
