<?php

namespace App\Filament\Pages\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Bridge only: validate with LoginRequest rules, hand off to AuthService.
 */
class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        Validator::validate($data, LoginRequest::rulesFor());

        try {
            app(AuthService::class)->attempt($data['email'], $data['password'], (bool) ($data['remember'] ?? false), request()->ip());
        } catch (ValidationException $e) {
            // Map service errors onto the form's state path so they show under the field.
            throw ValidationException::withMessages(['data.email' => $e->errors()['email']]);
        }

        return app(LoginResponse::class);
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()->rules(LoginRequest::rulesFor()['email']);
    }
}
