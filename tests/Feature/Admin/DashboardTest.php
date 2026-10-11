<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Widgets\LatestUsers;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Models\Role;
use App\Models\User;
use Filament\Widgets\AccountWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_stats_and_latest_users(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create(['name' => 'Ada Admin']));
        User::factory()->count(2)->create();

        $this->get('/')
            ->assertOk()
            ->assertSeeLivewire(StatsOverview::class)
            ->assertSeeLivewire(LatestUsers::class)
            ->assertDontSeeLivewire(AccountWidget::class);

        Livewire::test(StatsOverview::class)
            ->assertSee(__('app.dashboard.users'))
            ->assertSee('3')
            ->assertSee(__('app.dashboard.roles'))
            ->assertSee(__('app.dashboard.new_this_month'));
    }

    public function test_latest_users_lists_newest_five(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create(['created_at' => now()->subYear()]));
        $old = User::factory()->create(['created_at' => now()->subMonth()]);
        $new = User::factory()->count(5)->create();

        Livewire::test(LatestUsers::class)
            ->assertCanSeeTableRecords($new)
            ->assertCanNotSeeTableRecords([$old]);
    }

    public function test_widgets_hidden_without_view_permission(): void
    {
        $this->actingAs(User::factory()->withRole(Role::factory()->create())->create());

        $this->get('/')
            ->assertOk()
            ->assertDontSeeLivewire(StatsOverview::class)
            ->assertDontSeeLivewire(LatestUsers::class);
    }

    public function test_latest_users_empty_state_says_what_to_do(): void
    {
        // A role that can view users but no users to list besides... none: query excludes nothing, so remove all.
        $viewer = User::factory()->withRole(Role::factory()->withPermissions(['users' => ['view']])->create())->make();
        $this->actingAs($viewer);

        Livewire::test(LatestUsers::class)
            ->assertSee(__('app.dashboard.empty_heading'))
            ->assertSee(__('app.dashboard.empty_description'));
    }
}
