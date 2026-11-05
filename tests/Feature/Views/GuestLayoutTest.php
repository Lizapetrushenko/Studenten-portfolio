<?php

namespace Tests\Feature\Views;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GuestLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_layout_compiles_and_renders_authentication_slot(): void
    {
        $source = file_get_contents(resource_path('views/layouts/guest.blade.php'));
        $this->assertNotFalse($source);
        $this->assertNotEmpty(Blade::compileString($source));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="nl">', false)
            ->assertSee('<main class="auth-main">', false)
            ->assertSee('<div class="auth-card">', false)
            ->assertSee('Log in');
    }

    public function test_guest_sees_login_link_and_not_authenticated_logout_controls(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('Log in')
            ->assertDontSee('href="'.route('logout').'"', false);
    }
}
