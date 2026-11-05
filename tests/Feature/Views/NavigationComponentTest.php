<?php

namespace Tests\Feature\Views;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NavigationComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_navigation_contains_dashboard_profile_and_logout_links(): void
    {
        $user = User::factory()->create(['role' => 'student', 'name' => 'Student Voorbeeld']);
        $this->actingAs($user);

        $html = Blade::render("@include('layouts.navigation')");

        $this->assertStringContainsString('href="'.route('dashboard').'"', $html);
        $this->assertStringContainsString('href="'.route('profile.edit').'"', $html);
        $this->assertStringContainsString('action="'.route('logout').'"', $html);
        $this->assertStringContainsString('Student Voorbeeld', $html);
        $this->assertStringContainsString('Overzicht', $html);
    }
}
