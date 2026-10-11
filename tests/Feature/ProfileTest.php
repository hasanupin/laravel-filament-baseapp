<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\Role;
use App\Models\User;
use Filament\Auth\Notifications\NoticeOfEmailChangeRequest;
use Filament\Auth\Notifications\VerifyEmailChange;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // A limited role: the profile must not depend on module permissions.
        $this->user = User::factory()->withRole(Role::factory()->create())->create(['email' => 'me@example.com']);
        $this->actingAs($this->user);
    }

    public function test_user_menu_links_to_profile_page(): void
    {
        $this->assertSame(EditProfile::class, Filament::getPanel('admin')->getProfilePage());

        $this->get('/')->assertOk()->assertSee(EditProfile::getUrl());
        $this->get(EditProfile::getUrl())->assertOk();
    }

    public function test_name_change_needs_no_current_password(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Nama Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nama Baru', $this->user->fresh()->name);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'new-password-123', 'passwordConfirmation' => 'new-password-123', 'currentPassword' => 'wrong'])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'new-password-123', 'passwordConfirmation' => 'new-password-123', 'currentPassword' => 'password'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('new-password-123', $this->user->fresh()->password));
    }

    public function test_password_confirmation_must_match(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'new-password-123', 'passwordConfirmation' => 'different-123', 'currentPassword' => 'password'])
            ->call('save')
            ->assertHasFormErrors(['password']);
    }

    public function test_email_change_waits_for_verification_link(): void
    {
        Notification::fake();

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'new@example.com', 'currentPassword' => 'password'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Not changed yet: a link goes to the new address, a notice (with a block link) to the old one.
        $this->assertSame('me@example.com', $this->user->fresh()->email);
        Notification::assertSentTo($this->user, NoticeOfEmailChangeRequest::class);

        $url = null;
        Notification::assertSentOnDemand(VerifyEmailChange::class, function ($notification, $channels, AnonymousNotifiable $notifiable) use (&$url) {
            $url = $notification->url;

            return $notifiable->routes['mail'] === 'new@example.com';
        });

        $this->get($url)->assertRedirect();

        $this->assertSame('new@example.com', $this->user->fresh()->email);
    }

    public function test_email_change_requires_current_password(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'new@example.com', 'currentPassword' => ''])
            ->call('save')
            ->assertHasFormErrors(['currentPassword' => 'required']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'taken@example.com', 'currentPassword' => 'password'])
            ->call('save')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_verification_email_is_indonesian(): void
    {
        $notification = new VerifyEmailChange;
        $notification->url = 'http://localhost/verify';

        $mail = $notification->toMail($this->user);

        $this->assertSame('Verifikasi alamat email Anda', $mail->subject);
    }
}
