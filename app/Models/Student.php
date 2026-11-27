<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

/** @deprecated Use User with role=student; kept as a compatibility alias. */
class Student extends User
{
    protected $table = 'users';
    public ?int $legacyKlasId = null;

    protected $fillable = ['naam', 'name', 'email', 'password', 'role', 'klas_id', 'beoordelaar_docent_id', 'group_id'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Student $student): void {
            $student->name = $student->naam ?: $student->name;
            $student->role = 'student';
            $student->group_id = $student->klas_id ?: $student->group_id;
            $student->legacyKlasId = $student->klas_id ? (int) $student->klas_id : null;
            if (! $student->email) {
                $student->email = 'legacy-student-'.uniqid().'@example.test';
            }
            if (! $student->password) {
                $student->password = Hash::make('password');
            }
            $student->offsetUnset('naam');
            $student->offsetUnset('klas_id');
        });
        static::created(function (Student $student): void {
            $legacyGroupId = $student->legacyKlasId;
            if ($legacyGroupId) {
                $student->groups()->syncWithoutDetaching([$legacyGroupId]);
                $student->forceFill(['group_id' => $legacyGroupId])->saveQuietly();
            }
        });
    }

    public function klas(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function beoordelaar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'beoordelaar_docent_id');
    }
}
