<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\Menus\MenuResource;
use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, MenuSeeder::class]);
    }

    private function sidebar(User $user): string
    {
        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $start = strpos($html, 'fi-sidebar-nav');

        return substr($html, $start, strpos($html, '</nav>', $start) - $start);
    }

    private function assertUrlsInOrder(string $html, array $urls): void
    {
        $positions = array_map(fn ($url) => strpos($html, '"'.$url.'"'), $urls);

        $this->assertNotContains(false, $positions);
        $this->assertSame($positions, collect($positions)->sort()->values()->all());
    }

    public function test_settings_group_holds_users_roles_and_menu_in_order(): void
    {
        $sidebar = $this->sidebar(User::factory()->superAdmin()->create());

        $this->assertStringContainsString(__('app.menu_groups.settings'), $sidebar);
        $this->assertUrlsInOrder($sidebar, [UserResource::getUrl(), RoleResource::getUrl(), MenuResource::getUrl()]);
        $this->assertStringContainsString(__('app.modules.roles'), $sidebar);
        $this->assertStringContainsString(__('app.modules.menus'), $sidebar);
    }

    public function test_renamed_label_and_group_show_in_sidebar(): void
    {
        Menu::where('key', 'users')->update(['label' => 'Team members', 'group' => 'People']);

        $sidebar = $this->sidebar(User::factory()->superAdmin()->create());

        $this->assertStringContainsString('Team members', $sidebar);
        $this->assertStringContainsString('People', $sidebar);
        $this->get(UserResource::getUrl())->assertSeeInOrder(['<title>', 'Team Members', '</title>'], false);
    }

    public function test_sort_order_is_respected(): void
    {
        Menu::where('key', 'menus')->update(['sort' => 0]);

        $sidebar = $this->sidebar(User::factory()->superAdmin()->create());

        $this->assertUrlsInOrder($sidebar, [MenuResource::getUrl(), UserResource::getUrl(), RoleResource::getUrl()]);
    }

    public function test_inactive_item_is_hidden_but_page_still_opens(): void
    {
        Menu::where('key', 'roles')->update(['is_active' => false]);
        $user = User::factory()->superAdmin()->create();

        $this->assertStringNotContainsString(RoleResource::getUrl(), $this->sidebar($user));
        $this->get(RoleResource::getUrl())->assertOk();
    }

    public function test_staff_sees_only_users(): void
    {
        $staff = User::factory()->create(['role_id' => Role::firstWhere('name', 'Staff')->id]);

        $sidebar = $this->sidebar($staff);

        $this->assertStringContainsString(UserResource::getUrl(), $sidebar);
        $this->assertStringNotContainsString(RoleResource::getUrl(), $sidebar);
        $this->assertStringNotContainsString(MenuResource::getUrl(), $sidebar);
    }
}
