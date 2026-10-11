<?php

namespace Tests\Feature\Admin;

use App\Enums\Module;
use App\Filament\Resources\Menus\Pages\ManageMenus;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuResourceTest extends TestCase
{
    use RefreshDatabase;

    private Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->menu = Menu::factory()->forModule(Module::Users)->create(['sort' => 1]);
    }

    public function test_lists_menu_items(): void
    {
        Livewire::test(ManageMenus::class)->assertCanSeeTableRecords([$this->menu]);
    }

    public function test_edits_menu_item_in_modal(): void
    {
        Livewire::test(ManageMenus::class)
            ->callAction(TestAction::make(EditAction::class)->table($this->menu), data: [
                'label' => 'Team',
                'icon' => Heroicon::OutlinedUserGroup->value,
                'group' => null, // top level
                'sort' => 5,
                'is_active' => false,
            ])
            ->assertHasNoFormErrors();

        $menu = $this->menu->fresh();
        $this->assertSame(['Team', 'o-user-group', null, 5, false], [$menu->label, $menu->icon, $menu->group, $menu->sort, $menu->is_active]);
    }

    public function test_validates_label_and_icon(): void
    {
        Livewire::test(ManageMenus::class)
            ->callAction(TestAction::make(EditAction::class)->table($this->menu), data: [
                'label' => str_repeat('x', 51),
                'icon' => 'not-an-icon',
            ])
            ->assertHasFormErrors(['label' => 'max', 'icon']);
    }

    public function test_blank_label_falls_back_to_translated_default(): void
    {
        $this->menu->update(['label' => 'Team']);

        Livewire::test(ManageMenus::class)
            ->callAction(TestAction::make(EditAction::class)->table($this->menu), data: ['label' => ''])
            ->assertHasNoFormErrors();

        $this->assertNull($this->menu->fresh()->label);
        $this->assertSame(__('app.modules.users'), $this->menu->fresh()->displayLabel());
    }

    public function test_group_must_be_a_known_translated_group(): void
    {
        Livewire::test(ManageMenus::class)
            ->callAction(TestAction::make(EditAction::class)->table($this->menu), data: ['group' => 'anything'])
            ->assertHasFormErrors(['group' => 'in']);
    }

    public function test_has_no_create_or_delete(): void
    {
        Livewire::test(ManageMenus::class)
            ->assertActionDoesNotExist(CreateAction::class)
            ->assertActionDoesNotExist(TestAction::make(DeleteAction::class)->table($this->menu));
    }

    public function test_view_only_role_cannot_edit(): void
    {
        $this->actingAs(User::factory()->withRole(Role::factory()->withPermissions(['menus' => ['view']])->create())->create());

        Livewire::test(ManageMenus::class)
            ->assertActionHidden(TestAction::make(EditAction::class)->table($this->menu));
    }

    public function test_reorder_persists_sort(): void
    {
        $second = Menu::factory()->forModule(Module::Roles)->create(['sort' => 2]);

        Livewire::test(ManageMenus::class)
            ->call('reorderTable', [$second->getKey(), $this->menu->getKey()]);

        $this->assertSame(1, $second->fresh()->sort);
        $this->assertSame(2, $this->menu->fresh()->sort);
    }
}
