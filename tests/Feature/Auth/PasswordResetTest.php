<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders(): void
    {
        $this->get(Filament::getPanel('admin')->getRequestPasswordResetUrl())->assertOk();
    }

    public function test_reset_link_is_emailed(): void
    {
        Notification::fake();
        $user = User::factory()->superAdmin()->create();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_can_be_reset_with_token(): void
    {
        $user = User::factory()->superAdmin()->create();
        $token = Password::broker()->createToken($user);

        Livewire::withQueryParams(['email' => $user->email, 'token' => $token])
            ->test(ResetPassword::class)
            ->fillForm(['password' => 'new-password-123', 'passwordConfirmation' => 'new-password-123'])
            ->call('resetPassword')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
