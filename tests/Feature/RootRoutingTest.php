<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_on_root_is_sent_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_logged_in_user_on_root_sees_dashboard(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/')
            ->assertOk()
            ->assertSee(__('filament-panels::pages/dashboard.title'));
    }

    public function test_auth_pages_live_at_root(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/password-reset/request')->assertOk();
    }

    public function test_old_admin_prefix_is_gone(): void
    {
        $this->get('/admin')->assertNotFound();
    }

    public function test_brand_and_accent_are_set(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(config('app.name'))
            ->assertDontSee('fonts.googleapis.com'); // system font stack, no Inter download
    }
}
