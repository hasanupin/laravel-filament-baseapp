<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Filament\Admin\Resources\Users\Pages\ManageUsers;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRoleWith(array $permissions): User
    {
        $user = User::factory()->withRole(Role::factory()->withPermissions($permissions)->create())->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_view_only_user_sees_users_menu_but_not_roles(): void
    {
        $this->actingAsRoleWith(['users' => ['view']]);

        $this->get('/')
            ->assertOk()
            ->assertSee(UserResource::getUrl())
            ->assertDontSee(RoleResource::getUrl());

        $this->get(UserResource::getUrl())->assertOk();
        $this->get(RoleResource::getUrl())->assertForbidden();
    }

    public function test_view_only_user_has_no_create_edit_delete_buttons(): void
    {
        $this->actingAsRoleWith(['users' => ['view']]);
        $other = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->assertActionHidden(CreateAction::class)
            ->assertActionHidden(TestAction::make(EditAction::class)->table($other))
            ->assertActionHidden(TestAction::make(DeleteAction::class)->table($other));
    }

    public function test_ticked_actions_are_visible(): void
    {
        $this->actingAsRoleWith(['users' => ['view', 'create', 'update', 'delete']]);
        $other = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->assertActionVisible(CreateAction::class)
            ->assertActionVisible(TestAction::make(EditAction::class)->table($other))
            ->assertActionVisible(TestAction::make(DeleteAction::class)->table($other));
    }

    public function test_super_admin_sees_every_menu(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->get('/')
            ->assertSee(UserResource::getUrl())
            ->assertSee(RoleResource::getUrl());
        $this->get(RoleResource::getUrl())->assertOk();
    }

    public function test_user_without_role_cannot_enter_panel(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertForbidden();
    }
}
