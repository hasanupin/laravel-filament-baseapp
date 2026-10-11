<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user->hasPermission(Module::Users, PermissionAction::View)
            || $user->hasPermission(Module::Roles, PermissionAction::View);
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $monthStart = now()->startOfMonth();
        $stats = [];

        if ($user->hasPermission(Module::Users, PermissionAction::View)) {
            $stats[] = Stat::make(__('app.dashboard.users'), User::count())->url(UserResource::getUrl());
            $stats[] = Stat::make(__('app.dashboard.new_this_month'), User::where('created_at', '>=', $monthStart)->count())
                ->description(__('app.dashboard.since', ['date' => $monthStart->translatedFormat('j M')]));
        }

        if ($user->hasPermission(Module::Roles, PermissionAction::View)) {
            $stats[] = Stat::make(__('app.dashboard.roles'), Role::count())->url(RoleResource::getUrl());
        }

        return $stats;
    }
}
