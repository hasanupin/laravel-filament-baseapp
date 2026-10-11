<?php

namespace App\Services;

use App\Models\User;

/**
 * The signed-in user's own profile. Email changes don't come through here:
 * they go out as a verification link and are applied by Filament once the new address confirms it.
 */
class ProfileService
{
    /**
     * @param  array{name?: string, password?: string}  $data  password arrives already hashed from the form
     */
    public function update(User $user, array $data): User
    {
        // Role and email are never set from the profile form.
        $user->update(array_intersect_key($data, array_flip(['name', 'password'])));

        return $user;
    }
}
