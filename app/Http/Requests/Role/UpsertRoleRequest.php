<?php

namespace App\Http\Requests\Role;

use App\Enums\PermissionAction;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return static::rulesFor($this->route('role'));
    }

    /**
     * Shared by HTTP requests and the Filament modal form (Livewire has no FormRequest).
     *
     * @return array<string, list<mixed>>
     */
    public static function rulesFor(?Role $role = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role)],
            'is_super_admin' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => [Rule::enum(PermissionAction::class)],
        ];
    }
}
