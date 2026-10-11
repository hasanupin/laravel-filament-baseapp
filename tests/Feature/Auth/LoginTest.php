<?php

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_uses_custom_login_page(): void
    {
        $this->assertSame(Login::class, Filament::getPanel('admin')->getLoginRouteAction());
    }

    public function test_user_with_role_can_log_in(): void
    {
        $user = User::factory()->superAdmin()->create();

        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect(Filament::getUrl());

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->superAdmin()->create();

        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'wrong-password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_without_role_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_email_and_password_are_required(): void
    {
        Livewire::test(Login::class)
            ->fillForm(['email' => '', 'password' => ''])
            ->call('authenticate')
            ->assertHasFormErrors(['email' => 'required', 'password' => 'required']);
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $user = User::factory()->superAdmin()->create();

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->fillForm(['email' => $user->email, 'password' => 'wrong-password'])
                ->call('authenticate');
        }

        // Even the right password is refused while throttled.
        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }
}
