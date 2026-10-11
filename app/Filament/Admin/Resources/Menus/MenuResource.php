<?php

namespace App\Filament\Admin\Resources\Menus;

use App\Enums\Module;
use App\Filament\Admin\Resources\Menus\Pages\ManageMenus;
use App\Filament\Admin\Resources\Menus\Schemas\MenuForm;
use App\Filament\Admin\Resources\Menus\Tables\MenusTable;
use App\Filament\Shared\Concerns\HasManagedNavigation;
use App\Models\Menu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Wiring only: form in Schemas/, table in Tables/, writes in App\Services.
 */
class MenuResource extends Resource
{
    use HasManagedNavigation;

    protected static Module $module = Module::Menus;

    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    public static function getModelLabel(): string
    {
        return __('app.menus.model');
    }

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMenus::route('/'),
        ];
    }
}
