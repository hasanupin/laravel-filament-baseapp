<?php

namespace Database\Factories;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'is_super_admin' => false,
            'permissions' => [],
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(['is_super_admin' => true]);
    }

    /**
     * @param  array<string, list<string>>  $permissions  e.g. ['users' => ['view']]
     */
    public function withPermissions(array $permissions): static
    {
        return $this->state(['permissions' => $permissions]);
    }

    public function fullAccess(): static
    {
        $all = array_column(PermissionAction::cases(), 'value');

        return $this->withPermissions(
            array_fill_keys(array_column(Module::cases(), 'value'), $all),
        );
    }
}
