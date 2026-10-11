<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Http\Requests\User\UpsertUserRequest;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $rules = fn (string $field) => fn (?User $record) => UpsertUserRequest::rulesFor($record)[$field];

        return $schema
            ->components([
                TextInput::make('name')->label(__('app.users.fields.name'))->required()->rules($rules('name')),
                TextInput::make('email')->label(__('app.users.fields.email'))->email()->required()->rules($rules('email')),
                Select::make('role_id')
                    ->label(__('app.users.fields.role'))
                    ->relationship('role', 'name')
                    ->preload()
                    ->required()
                    ->rules($rules('role_id')),
                TextInput::make('password')
                    ->label(__('app.users.fields.password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText(fn (string $operation) => $operation === 'edit' ? __('app.users.password_keep') : null)
                    ->rules($rules('password'))
                    ->live(onBlur: true),
                // Typed twice so a typo can't lock the user out; never saved.
                TextInput::make('password_confirmation')
                    ->label(__('app.users.fields.password_confirmation'))
                    ->password()
                    ->revealable()
                    ->required(fn (Get $get) => filled($get('password')))
                    ->rules($rules('password_confirmation'))
                    ->dehydrated(false),
            ]);
    }
}
