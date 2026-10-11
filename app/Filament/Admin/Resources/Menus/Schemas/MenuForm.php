<?php

namespace App\Filament\Admin\Resources\Menus\Schemas;

use App\Filament\Shared\Forms\HeroiconSelect;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Models\Menu;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        $rules = UpdateMenuRequest::rulesFor();

        return $schema
            ->components([
                TextInput::make('label')
                    ->label(__('app.menus.fields.label'))
                    ->placeholder(fn (?Menu $record) => $record?->module()?->getLabel())
                    ->helperText(__('app.menus.label_help'))
                    ->maxLength(50)
                    ->rules($rules['label']),
                HeroiconSelect::make('icon')
                    ->label(__('app.menus.fields.icon'))
                    ->required()
                    ->rules($rules['icon']),
                // Groups are translation keys (lang/*/app.php → menu_groups), so they follow the language too.
                Select::make('group')
                    ->label(__('app.menus.fields.group'))
                    ->options(fn () => __('app.menu_groups'))
                    ->placeholder(__('app.menus.top_level'))
                    ->helperText(__('app.menus.group_help'))
                    ->rules($rules['group']),
                TextInput::make('sort')->label(__('app.menus.fields.sort'))->numeric()->required()->minValue(0)->rules($rules['sort']),
                Toggle::make('is_active')
                    ->label(__('app.menus.fields.is_active'))
                    ->helperText(__('app.menus.is_active_help'))
                    ->rules($rules['is_active']),
            ]);
    }
}
