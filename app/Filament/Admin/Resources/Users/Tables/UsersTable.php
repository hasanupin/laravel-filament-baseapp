<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Filament\Shared\Actions\ServiceDeleteAction;
use App\Models\User;
use App\Services\UserService;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('role'))
            ->columns([
                TextColumn::make('name')->label(__('app.users.fields.name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('app.users.fields.email'))->searchable(),
                TextColumn::make('role.name')->label(__('app.users.fields.role'))->badge(),
                TextColumn::make('created_at')->label(__('app.users.fields.created_at'))->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->using(fn (User $record, array $data) => app(UserService::class)->update($record, $data)),
                ServiceDeleteAction::make(fn (User $record) => app(UserService::class)->delete($record)),
            ]);
    }
}
