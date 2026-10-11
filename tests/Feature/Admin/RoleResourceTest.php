<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_creates_role_with_checkbox_permissions(): void
    {
        Livewire::test(ManageRoles::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Editor',
                'is_super_admin' => false,
                'permissions' => ['users' => ['view', 'update'], 'roles' => ['view']],
            ])
            ->assertHasNoFormErrors();

        $role = Role::firstWhere('name', 'Editor');
        $this->assertEquals(['users' => ['view', 'update'], 'roles' => ['view'], 'menus' => []], $role->permissions);
    }

    public function test_name_is_required_and_unique(): void
    {
        $existing = Role::factory()->create();

        Livewire::test(ManageRoles::class)
            ->callAction(CreateAction::class, data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        Livewire::test(ManageRoles::class)
            ->callAction(CreateAction::class, data: ['name' => $existing->name])
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_edits_role_permissions(): void
    {
        $role = Role::factory()->fullAccess()->create();

        Livewire::test(ManageRoles::class)
            ->callAction(TestAction::make(EditAction::class)->table($role), data: [
                'name' => $role->name,
                'permissions' => ['users' => ['view'], 'roles' => [], 'menus' => []],
            ])
            ->assertHasNoFormErrors();

        $this->assertEquals(['users' => ['view'], 'roles' => [], 'menus' => []], $role->fresh()->permissions);
    }

    public function test_user_menu_row_offers_only_view_and_update(): void
    {
        Livewire::test(ManageRoles::class)
            ->mountAction(CreateAction::class)
            ->assertFormFieldExists('permissions.menus', fn ($field) => array_keys($field->getOptions()) === ['view', 'update']);
    }

    public function test_role_in_use_is_not_deleted(): void
    {
        $role = Role::factory()->create();
        User::factory()->withRole($role)->create();

        Livewire::test(ManageRoles::class)
            ->callAction(TestAction::make(DeleteAction::class)->table($role));

        $this->assertModelExists($role);
    }
}
