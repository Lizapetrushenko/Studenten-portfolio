<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $admin = User::firstOrNew(['email' => 'admin@admin.com']);

        if (! $admin->exists) {
            $admin->forceFill([
                'name' => 'Admin User',
                'password' => Hash::make('qwerty123'),
                'email_verified_at' => now(),
            ]);
        }

        // Administrator permissions are assigned here, never through registration.
        $admin->forceFill(['is_admin' => true, 'role' => 'admin', 'education' => null])->save();
    }
}