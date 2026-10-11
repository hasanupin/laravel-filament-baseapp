<?php

namespace App\Filament\Resources\Menus;

use App\Enums\Module;
use App\Filament\Concerns\HasManagedNavigation;
use App\Filament\Resources\Menus\Pages\ManageMenus;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
        $rules = UpdateMenuRequest::rulesFor();

        return $schema
            ->components([
                TextInput::make('label')
                    ->label(__('app.menus.fields.label'))
                    ->placeholder(fn (?Menu $record) => $record?->module()?->label())
                    ->helperText(__('app.menus.label_help'))
                    ->maxLength(50)
                    ->rules($rules['label']),
                Select::make('icon')
                    ->label(__('app.menus.fields.icon'))
                    ->required()
                    ->searchable()
                    ->options(static::iconOptions())
                    ->allowHtml()
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

    public static function table(Table $table): Table
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
                    ->disabled(fn () => ! auth()->user()->can('update', Menu::class)),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(fn (Menu $record, array $data) => app(MenuService::class)->update($record, $data)),
            ]);
    }

    /**
     * Outlined heroicons only, so the sidebar stays one visual weight.
     *
     * @return array<string, string>
     */
    public static function iconOptions(): array
    {
        return once(fn () => collect(Heroicon::cases())
            ->filter(fn (Heroicon $icon) => str_starts_with($icon->value, 'o-'))
            ->mapWithKeys(fn (Heroicon $icon) => [
                // Inline style: app Tailwind classes aren't in Filament's compiled CSS.
                $icon->value => '<span style="display:inline-flex;align-items:center;gap:.5rem">'
                    .svg('heroicon-'.$icon->value, '', ['style' => 'width:1.25rem;height:1.25rem;flex-shrink:0'])->toHtml()
                    .e(Str::of($icon->value)->after('o-')->replace('-', ' '))
                    .'</span>',
            ])
            ->all());
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMenus::route('/'),
        ];
    }
}
