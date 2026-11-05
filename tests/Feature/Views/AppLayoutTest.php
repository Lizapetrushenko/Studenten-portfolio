<?php

namespace Tests\Feature\Views;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_layout_compiles_and_renders_authenticated_slot_content(): void
    {
        $source = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $this->assertNotFalse($source);
        $this->assertNotEmpty(Blade::compileString($source));

        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('<main>', false)
            ->assertSee('Gegevens')
            ->assertSee('portal-header-wrapper');
    }
}
