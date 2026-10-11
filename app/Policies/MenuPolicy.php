<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;

/**
 * Menu rows map 1:1 to modules in code, so they can be edited but never created or deleted.
 */
class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Module::Menus, PermissionAction::View);
    }

    public function view(User $user): bool
    {
        return $user->hasPermission(Module::Menus, PermissionAction::View);
    }

    public function update(User $user): bool
    {
        return $user->hasPermission(Module::Menus, PermissionAction::Update);
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermission(Module::Menus, PermissionAction::Update);
    }

    public function create(): bool
    {
        return false;
    }

    public function delete(): bool
    {
        return false;
    }

    public function deleteAny(): bool
    {
        return false;
    }
}
