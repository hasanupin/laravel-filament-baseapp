<?php

namespace App\Services;

use App\Enums\Module;
use App\Models\Role;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function create(array $data): Role
    {
        return Role::create($this->normalize($data));
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($this->normalize($data));

        return $role;
    }

    public function delete(Role $role): void
    {
        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => __('app.roles.in_use')]);
        }

        $role->delete();
    }

    /**
     * Keep only known modules/actions, so the JSON always has one key per module.
     */
    private function normalize(array $data): array
    {
        if (! array_key_exists('permissions', $data)) {
            return $data;
        }

        $permissions = [];

        foreach (Module::cases() as $module) {
            $permissions[$module->value] = array_values(array_intersect(
                $data['permissions'][$module->value] ?? [],
                array_column($module->actions(), 'value'),
            ));
        }

        return ['permissions' => $permissions] + $data;
    }
}
