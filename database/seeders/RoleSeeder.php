<?php

namespace Database\Seeders;

use App\Enums\PermissionAction;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = array_column(PermissionAction::cases(), 'value');

        Role::updateOrCreate(['name' => 'Super Admin'], ['is_super_admin' => true, 'permissions' => []]);

        Role::updateOrCreate(['name' => 'Admin'], [
            'is_super_admin' => false,
            'permissions' => [
                'users' => $all,
                'roles' => $all,
                'menus' => [PermissionAction::View->value, PermissionAction::Update->value],
            ],
        ]);

        Role::updateOrCreate(['name' => 'Staff'], [
            'is_super_admin' => false,
            'permissions' => ['users' => [PermissionAction::View->value], 'roles' => [], 'menus' => []],
        ]);
    }
}
