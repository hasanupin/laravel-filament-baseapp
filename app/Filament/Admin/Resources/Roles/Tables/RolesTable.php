<?php

namespace App\Filament\Admin\Resources\Roles\Tables;

use App\Filament\Shared\Actions\ServiceDeleteAction;
use App\Models\Role;
use App\Services\RoleService;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
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
                ServiceDeleteAction::make(fn (Role $record) => app(RoleService::class)->delete($record)),
            ]);
    }
}
