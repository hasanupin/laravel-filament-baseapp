<?php

namespace App\Filament\Resources\Roles;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Filament\Concerns\HasManagedNavigation;
use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Http\Requests\Role\UpsertRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

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
        $rules = fn (string $field) => fn (?Role $record) => UpsertRoleRequest::rulesFor($record)[$field];

        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')->label(__('app.roles.fields.name'))->required()->rules($rules('name')),
                Toggle::make('is_super_admin')
                    ->label(__('app.roles.fields.is_super_admin'))
                    ->helperText(__('app.roles.super_admin_help'))
                    ->live(),
                Section::make(__('app.roles.permissions'))
                    ->description(__('app.roles.permissions_help'))
                    ->hidden(fn (Get $get) => $get('is_super_admin'))
                    ->schema(array_map(
                        fn (Module $module) => CheckboxList::make("permissions.{$module->value}")
                            ->label($module->label())
                            ->options(PermissionAction::options($module->actions()))
                            ->columns(4)
                            ->gridDirection('row')
                            ->bulkToggleable(),
                        Module::cases(),
                    )),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('app.roles.fields.name'))->searchable()->sortable(),
                IconColumn::make('is_super_admin')->label(__('app.roles.fields.is_super_admin'))->boolean(),
                TextColumn::make('users_count')->counts('users')->label(__('app.roles.fields.users_count')),
                TextColumn::make('updated_at')->label(__('app.roles.fields.updated_at'))->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(fn (Role $record, array $data) => app(RoleService::class)->update($record, $data)),
                DeleteAction::make()
                    ->using(function (Role $record): bool {
                        try {
                            app(RoleService::class)->delete($record);
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return false;
                        }

                        return true;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
