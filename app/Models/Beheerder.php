<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

/** @deprecated Use User with role=admin; kept as a compatibility alias. */
class Beheerder extends User
{
    protected $table = 'users';

    protected $fillable = ['naam', 'name', 'email', 'password', 'role'];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Beheerder $administrator): void {
            $administrator->name = $administrator->naam ?: $administrator->name;
            $administrator->role = 'admin';
            if (! $administrator->email) {
                $administrator->email = 'legacy-admin-'.uniqid().'@example.test';
            }
            if (! $administrator->password) {
                $administrator->password = Hash::make('password');
            }
            $administrator->offsetUnset('naam');
        });
    }
}
