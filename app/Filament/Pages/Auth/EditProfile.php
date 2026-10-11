<?php

namespace App\Filament\Pages\Auth;

use App\Services\ProfileService;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

/**
 * Bridge only: Filament validates (current password, unique email, password rules) and
 * sends the email-change verification; ProfileService saves the rest.
 */
class EditProfile extends BaseEditProfile
{
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        if (array_key_exists('email', $data)) {
            $this->sendEmailChangeVerification($record, $data['email']);
            unset($data['email']);
        }

        return app(ProfileService::class)->update($record, $data);
    }

    // Always shown (Filament hides it until a new password is typed), so users see they must type it twice.
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()
            ->label(__('app.profile.password_confirmation'))
            ->visible(true)
            ->required(fn (Get $get): bool => filled($get('password')));
    }
}
