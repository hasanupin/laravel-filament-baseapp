<?php

namespace App\Filament\Admin\Resources\Menus\Tables;

use App\Models\Menu;
use App\Services\MenuService;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort', fn () => auth()->user()->can('reorder', Menu::class))
            ->paginated(false)
            ->columns([
                IconColumn::make('icon')
                    ->label(__('app.menus.fields.icon'))
                    ->icon(fn (Menu $record) => Heroicon::tryFrom($record->icon)),
                TextColumn::make('label')
                    ->label(__('app.menus.fields.label'))
                    ->state(fn (Menu $record) => $record->displayLabel())
                    ->description(fn (Menu $record) => __('app.menus.module', ['key' => $record->key])),
                TextColumn::make('group')
                    ->label(__('app.menus.fields.group'))
                    ->state(fn (Menu $record) => $record->displayGroup())
                    ->placeholder(__('app.menus.top_level')),
                TextColumn::make('sort')->label(__('app.menus.fields.sort'))->numeric(),
                ToggleColumn::make('is_active')
                    ->label(__('app.menus.fields.in_sidebar'))
                    ->disabled(fn () => ! auth()->user()->can('update', Menu::class))
                    // Saves through the service (and its rules), not straight to the model.
                    ->updateStateUsing(fn (Menu $record, bool $state) => app(MenuService::class)->update($record, ['is_active' => $state])->is_active),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(fn (Menu $record, array $data) => app(MenuService::class)->update($record, $data)),
            ]);
    }
}
