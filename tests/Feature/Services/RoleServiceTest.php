<?php

namespace Tests\Feature\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_stores_only_known_modules_and_actions(): void
    {
        $role = app(RoleService::class)->create([
            'name' => 'Editor',
            'is_super_admin' => false,
            'permissions' => [
                'users' => ['view', 'update', 'hack'],
                'roles' => [],
                'unknown' => ['view'],
            ],
        ]);

        $this->assertEquals(['users' => ['view', 'update'], 'roles' => [], 'menus' => []], $role->permissions);
    }

    public function test_update_replaces_permissions(): void
    {
        $role = Role::factory()->fullAccess()->create();

        app(RoleService::class)->update($role, ['permissions' => ['users' => ['view']]]);

        $this->assertEquals(['users' => ['view'], 'roles' => [], 'menus' => []], $role->fresh()->permissions);
    }

    public function test_menus_only_keep_view_and_update(): void
    {
        $role = app(RoleService::class)->create([
            'name' => 'Menu editor',
            'permissions' => ['menus' => ['view', 'create', 'update', 'delete']],
        ]);

        $this->assertSame(['view', 'update'], $role->permissions['menus']);
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $role = Role::factory()->create();
        User::factory()->withRole($role)->create();

        $this->expectException(ValidationException::class);

        app(RoleService::class)->delete($role);
    }

    public function test_unused_role_can_be_deleted(): void
    {
        $role = Role::factory()->create();

        app(RoleService::class)->delete($role);

        $this->assertModelMissing($role);
    }
}
