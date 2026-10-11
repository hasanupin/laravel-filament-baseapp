<?php

namespace App\Filament\Admin\Resources\Users;

use App\Enums\Module;
use App\Filament\Admin\Resources\Users\Pages\ManageUsers;
use App\Filament\Admin\Resources\Users\Schemas\UserForm;
use App\Filament\Admin\Resources\Users\Tables\UsersTable;
use App\Filament\Shared\Concerns\HasManagedNavigation;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Wiring only: form in Schemas/, table in Tables/, writes in App\Services.
 */
class UserResource extends Resource
{
    use HasManagedNavigation;

    protected static Module $module = Module::Users;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getModelLabel(): string
    {
        return __('app.users.model');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
