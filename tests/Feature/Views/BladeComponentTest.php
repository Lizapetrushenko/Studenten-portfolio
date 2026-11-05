<?php

namespace Tests\Feature\Views;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BladeComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_button_link_and_navigation_components_render_slots_and_attributes(): void
    {
        $this->assertStringContainsString('Opslaan', Blade::render('<x-primary-button>Opslaan</x-primary-button>'));
        $this->assertStringContainsString('Annuleren', Blade::render('<x-secondary-button>Annuleren</x-secondary-button>'));
        $this->assertStringContainsString('Verwijderen', Blade::render('<x-danger-button>Verwijderen</x-danger-button>'));
        $this->assertStringContainsString('Profiel', Blade::render('<x-dropdown-link href="/profile">Profiel</x-dropdown-link>'));
        $this->assertStringContainsString('Dashboard', Blade::render('<x-nav-link href="/dashboard" :active="true">Dashboard</x-nav-link>'));
        $this->assertStringContainsString('Dashboard', Blade::render('<x-responsive-nav-link href="/dashboard" :active="false">Dashboard</x-responsive-nav-link>'));
    }

    public function test_form_and_status_components_render_labels_errors_values_and_messages(): void
    {
        $label = Blade::render('<x-input-label for="email" value="E-mailadres" />');
        $input = Blade::render('<x-text-input id="email" type="email" name="email" disabled />');
        $errors = Blade::render('<x-input-error :messages="[\'Ongeldig e-mailadres\']" />');
        $status = Blade::render('<x-auth-session-status status="Opgeslagen" />');

        $this->assertStringContainsString('for="email"', $label);
        $this->assertStringContainsString('E-mailadres', $label);
        $this->assertStringContainsString('disabled', $input);
        $this->assertStringContainsString('Ongeldig e-mailadres', $errors);
        $this->assertStringContainsString('Opgeslagen', $status);
    }

    public function test_dropdown_modal_logo_and_group_status_components_render_expected_markup(): void
    {
        $dropdown = Blade::render(<<<'BLADE'
<x-dropdown>
    <x-slot:trigger>Menu</x-slot:trigger>
    <x-slot:content>Instellingen</x-slot:content>
</x-dropdown>
BLADE);
        $modal = Blade::render('<x-modal name="confirm">Bevestigen</x-modal>');
        $logo = Blade::render('<x-application-logo />');
        $photo = Blade::render('<x-group-status-photo status="nieuw" />');

        $this->assertStringContainsString('Menu', $dropdown);
        $this->assertStringContainsString('Instellingen', $dropdown);
        $this->assertStringContainsString('Bevestigen', $modal);
        $this->assertStringContainsString('<svg', $logo);
        $this->assertStringContainsString('Nieuwe student', $photo);
    }

    public function test_mfa_menu_switches_between_setup_and_disable_actions(): void
    {
        $user = User::factory()->create(['role' => 'student', 'mfa_enabled' => false]);
        $this->actingAs($user);
        $setup = Blade::render('<x-mfa-menu />');
        $this->assertStringContainsString('MFA instellen', $setup);

        $user->forceFill(['mfa_enabled' => true])->save();
        $disable = Blade::render('<x-mfa-menu />');
        $this->assertStringContainsString('MFA uitschakelen', $disable);
        $this->assertStringContainsString('action="'.route('mfa.disable').'"', $disable);
    }
}
