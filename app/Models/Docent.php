<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

/** @deprecated Use User with role=teacher; kept as a compatibility alias. */
class Docent extends User
{
    protected $table = 'users';

    protected $fillable = ['naam', 'name', 'email', 'password', 'role'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Docent $teacher): void {
            $teacher->name = $teacher->naam ?: $teacher->name;
            $teacher->role = 'teacher';
            if (! $teacher->email) {
                $teacher->email = 'legacy-teacher-'.uniqid().'@example.test';
            }
            if (! $teacher->password) {
                $teacher->password = Hash::make('password');
            }
            $teacher->offsetUnset('naam');
        });
    }
}
