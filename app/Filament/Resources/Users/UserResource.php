<?php

namespace App\Filament\Resources\Users;

use App\Enums\Module;
use App\Filament\Concerns\HasManagedNavigation;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Http\Requests\User\UpsertUserRequest;
use App\Models\User;
use App\Services\UserService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    use HasManagedNavigation;

    protected static Module $module = Module::Users;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getModelLabel(): string
    {
        return __('app.users.model');
    }

    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
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
                DeleteAction::make()
                    ->using(function (User $record): bool {
                        try {
                            app(UserService::class)->delete($record);
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
            'index' => ManageUsers::route('/'),
        ];
    }
}
