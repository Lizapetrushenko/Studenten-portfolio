<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_welcome_and_screen_index_pages_render_successfully(): void
    {
        $this->get('/')->assertOk()->assertSee('BEWIJSSTUKKENPLAN')->assertSee('Log in');
        $this->get(route('screens.index'))->assertOk();
    }

    public function test_application_health_route_returns_success(): void
    {
        $this->get('/up')->assertOk();
    }
}
