<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Module::Users, PermissionAction::View);
    }

    public function view(User $user): bool
    {
        return $user->hasPermission(Module::Users, PermissionAction::View);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Module::Users, PermissionAction::Create);
    }

    public function update(User $user): bool
    {
        return $user->hasPermission(Module::Users, PermissionAction::Update);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission(Module::Users, PermissionAction::Delete);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission(Module::Users, PermissionAction::Delete);
    }
}
