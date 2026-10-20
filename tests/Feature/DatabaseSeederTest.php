<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_an_admin_and_can_be_run_again(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertFalse(User::where('email', 'test@example.com')->firstOrFail()->is_admin);

        $admin->password = Hash::make('changed-password');
        $admin->save();
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 2);
        $this->assertTrue(Hash::check('changed-password', $admin->fresh()->password));
    }

    public function test_registration_cannot_grant_admin_permissions(): void
    {
        $this->post('/register', [
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'leerweg' => 'BOL',
            'is_admin' => true,
        ])->assertSessionHasNoErrors();

        $this->assertFalse(User::where('email', 'student@example.com')->firstOrFail()->is_admin);
    }
}