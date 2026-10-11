<?php

namespace App\Filament\Admin\Resources\Roles;

use App\Enums\Module;
use App\Filament\Admin\Resources\Roles\Pages\ManageRoles;
use App\Filament\Admin\Resources\Roles\Schemas\RoleForm;
use App\Filament\Admin\Resources\Roles\Tables\RolesTable;
use App\Filament\Shared\Concerns\HasManagedNavigation;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Wiring only: form in Schemas/, table in Tables/, writes in App\Services.
 */
class RoleResource extends Resource
{
    use HasManagedNavigation;

    protected static Module $module = Module::Roles;

    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function getModelLabel(): string
    {
        return __('app.roles.model');
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
