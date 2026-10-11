<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attempt_logs_in_valid_user(): void
    {
        $user = User::factory()->superAdmin()->create();

        app(AuthService::class)->attempt($user->email, 'password', false, '127.0.0.1');

        $this->assertAuthenticatedAs($user);
    }

    public function test_attempt_throws_on_bad_credentials(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->expectException(ValidationException::class);

        app(AuthService::class)->attempt($user->email, 'nope', false, '127.0.0.1');
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        $user = User::factory()->superAdmin()->create();
        $service = app(AuthService::class);

        for ($i = 0; $i < 4; $i++) {
            try {
                $service->attempt($user->email, 'nope', false, '127.0.0.1');
            } catch (ValidationException) {
            }
        }

        $service->attempt($user->email, 'password', false, '127.0.0.1');
        auth()->logout();

        // Counter was reset, so another failure is a normal failure, not a lockout.
        try {
            $service->attempt($user->email, 'nope', false, '127.0.0.1');
        } catch (ValidationException) {
        }
        $service->attempt($user->email, 'password', false, '127.0.0.1');

        $this->assertAuthenticatedAs($user);
    }
}
