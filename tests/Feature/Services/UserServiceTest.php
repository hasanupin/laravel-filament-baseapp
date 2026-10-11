<?php

namespace Tests\Feature\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_hashes_password_and_sets_role(): void
    {
        $role = Role::factory()->create();

        $user = app(UserService::class)->create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret-123',
            'role_id' => $role->id,
        ]);

        $this->assertTrue(Hash::check('secret-123', $user->password));
        $this->assertTrue($user->role->is($role));
    }

    public function test_update_with_blank_password_keeps_old_password(): void
    {
        $user = User::factory()->superAdmin()->create();
        $oldHash = $user->password;

        app(UserService::class)->update($user, ['name' => 'Renamed', 'password' => null]);

        $this->assertSame('Renamed', $user->fresh()->name);
        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_update_with_new_password_changes_it(): void
    {
        $user = User::factory()->superAdmin()->create();

        app(UserService::class)->update($user, ['password' => 'changed-123']);

        $this->assertTrue(Hash::check('changed-123', $user->fresh()->password));
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $this->expectException(ValidationException::class);

        app(UserService::class)->delete($user);
    }

    public function test_delete_removes_other_user(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $other = User::factory()->create();

        app(UserService::class)->delete($other);

        $this->assertModelMissing($other);
    }
}
