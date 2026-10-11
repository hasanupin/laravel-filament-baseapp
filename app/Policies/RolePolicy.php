<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Module::Roles, PermissionAction::View);
    }

    public function view(User $user): bool
    {
        return $user->hasPermission(Module::Roles, PermissionAction::View);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Module::Roles, PermissionAction::Create);
    }

    public function update(User $user): bool
    {
        return $user->hasPermission(Module::Roles, PermissionAction::Update);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission(Module::Roles, PermissionAction::Delete);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission(Module::Roles, PermissionAction::Delete);
    }
}
