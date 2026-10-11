<?php

namespace Tests\Feature;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_sample_roles_and_logins(): void
    {
        $this->seed();
        $this->seed(); // re-runnable

        $this->assertSame(3, Role::count());
        $this->assertTrue(Role::firstWhere('name', 'Super Admin')->is_super_admin);

        $admin = User::firstWhere('email', 'admin@example.com');
        $this->assertTrue($admin->hasPermission(Module::Roles, PermissionAction::Delete));
        $this->assertFalse($admin->isSuperAdmin());

        $staff = User::firstWhere('email', 'staff@example.com');
        $this->assertTrue($staff->hasPermission(Module::Users, PermissionAction::View));
        $this->assertFalse($staff->hasPermission(Module::Users, PermissionAction::Create));
        $this->assertFalse($staff->hasPermission(Module::Roles, PermissionAction::View));

        $this->assertTrue(User::firstWhere('email', 'superadmin@example.com')->isSuperAdmin());
        $this->assertSame(13, User::count());

        $this->assertSame(['users', 'roles', 'menus'], Menu::orderBy('sort')->pluck('key')->all());
        // Blank label = translated default, so the sidebar follows the active language.
        $this->assertSame([null, null, null], Menu::orderBy('sort')->pluck('label')->all());
        $this->assertSame(['settings'], Menu::distinct()->pluck('group')->all());
        $this->assertTrue($admin->hasPermission(Module::Menus, PermissionAction::Update));
        $this->assertFalse($staff->hasPermission(Module::Menus, PermissionAction::View));
    }

    public function test_reseeding_keeps_renamed_menu_labels(): void
    {
        $this->seed();
        Menu::where('key', 'users')->update(['label' => 'Team']);

        $this->seed();

        $this->assertSame('Team', Menu::firstWhere('key', 'users')->label);
    }
}
