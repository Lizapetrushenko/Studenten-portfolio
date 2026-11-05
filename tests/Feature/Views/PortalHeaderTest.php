<?php

namespace Tests\Feature\Views;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_header_shows_role_dashboard_user_information_and_logout(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Docent Voorbeeld']);

        $this->actingAs($teacher)->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('<header class="portal-header-wrapper', false)
            ->assertSee('href="'.route('teacher.dashboard').'"', false)
            ->assertSee('Docent Voorbeeld')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Meer over portfolio')
            ->assertSee('Docentdashboard');
    }
}
