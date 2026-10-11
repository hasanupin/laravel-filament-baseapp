<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Http\Requests\Role\UpsertRoleRequest;
use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
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
                // One checkbox row per module; actions come from Module::actions().
                Section::make(__('app.roles.permissions'))
                    ->description(__('app.roles.permissions_help'))
                    ->hidden(fn (Get $get) => $get('is_super_admin'))
                    ->schema(array_map(
                        fn (Module $module) => CheckboxList::make("permissions.{$module->value}")
                            ->label($module->getLabel())
                            ->options(PermissionAction::options($module->actions()))
                            ->columns(4)
                            ->gridDirection('row')
                            ->bulkToggleable(),
                        Module::cases(),
                    )),
            ]);
    }
}
