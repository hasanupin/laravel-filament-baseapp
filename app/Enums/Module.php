<?php

namespace App\Enums;

/**
 * Every admin module that can be granted to a role. Adding a case here adds a row to the role permission grid.
 */
enum Module: string
{
    case Users = 'users';
    case Roles = 'roles';
    case Menus = 'menus';

    public function label(): string
    {
        return __("app.modules.{$this->value}");
    }

    /**
     * Actions that make sense for this module; menu rows are tied to code, so they can't be created or deleted.
     *
     * @return list<PermissionAction>
     */
    public function actions(): array
    {
        return match ($this) {
            self::Menus => [PermissionAction::View, PermissionAction::Update],
            default => PermissionAction::cases(),
        };
    }
}
