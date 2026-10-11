<?php

namespace App\Filament\Widgets;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * The dashboard's one question: who joined recently, and do they have the right role?
 */
class LatestUsers extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('app.dashboard.newest_users');
    }

    public static function canView(): bool
    {
        return auth()->user()->hasPermission(Module::Users, PermissionAction::View);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with('role')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label(__('app.users.fields.name')),
                TextColumn::make('email')->label(__('app.users.fields.email'))->visibleFrom('md'),
                TextColumn::make('role.name')->label(__('app.users.fields.role'))->badge()->color('gray'),
                TextColumn::make('created_at')->label(__('app.dashboard.joined'))->since(),
            ])
            ->emptyStateHeading(__('app.dashboard.empty_heading'))
            ->emptyStateDescription(__('app.dashboard.empty_description'));
    }
}
