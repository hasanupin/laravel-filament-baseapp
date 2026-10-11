<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        $logins = [
            'superadmin@example.com' => ['Super Admin', 'Super Admin'],
            env('SEED_ADMIN_EMAIL', 'admin@example.com') => ['Admin', 'Admin'],
            'staff@example.com' => ['Staff', 'Staff'],
        ];

        foreach ($logins as $email => [$name, $role]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => $role === 'Admin' ? env('SEED_ADMIN_PASSWORD', 'password') : 'password',
                'role_id' => $roles[$role],
                'email_verified_at' => now(),
            ]);
        }

        // Filler rows for the Users table and dashboard stats; only on the first run.
        if (User::count() === count($logins)) {
            User::factory()->count(10)->create(['role_id' => $roles['Staff']]);
        }
    }
}
