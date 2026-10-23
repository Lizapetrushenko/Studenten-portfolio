<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_new_users_can_register_with_each_leerweg(): void
    {
        foreach (['BOL', 'BBL', 'FLEX'] as $leerweg) {
            $email = strtolower($leerweg).'@example.com';
            $response = $this->post('/register', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'leerweg' => $leerweg,
            ]);

            $this->assertAuthenticated();
            $response->assertRedirect(route('dashboard', absolute: false));
            $this->assertDatabaseHas('users', ['email' => $email, 'leerweg' => $leerweg, 'student_number' => strtolower($leerweg), 'education' => 'Software Developer']);
            $this->post('/logout');
        }
    }

    public function test_registration_requires_a_valid_leerweg(): void
    {
        foreach ([null, 'INVALID'] as $leerweg) {
            $response = $this->post('/register', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'leerweg' => $leerweg,
            ]);

            $response->assertSessionHasErrors('leerweg');
            $this->assertGuest();
            $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        }
    }
}