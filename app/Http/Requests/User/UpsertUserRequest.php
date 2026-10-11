<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpsertUserRequest extends FormRequest
{
    public function rules(): array
    {
        return static::rulesFor($this->route('user'));
    }

    /**
     * Shared by HTTP requests and the Filament modal form (Livewire has no FormRequest).
     *
     * @return array<string, list<mixed>>
     */
    public static function rulesFor(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::min(8), 'confirmed'],
            'password_confirmation' => ['required_with:password'],
        ];
    }
}
