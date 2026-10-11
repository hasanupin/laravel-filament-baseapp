<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        // Blank password on edit means "keep the current one".
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        if ($user->is(auth()->user())) {
            throw ValidationException::withMessages(['user' => __('app.users.cannot_delete_self')]);
        }

        $user->delete();
    }
}
