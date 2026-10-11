<?php

namespace App\Enums;

enum PermissionAction: string
{
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';

    /**
     * @param  list<self>|null  $actions  defaults to every action
     * @return array<string, string>
     */
    public static function options(?array $actions = null): array
    {
        return collect($actions ?? self::cases())
            ->mapWithKeys(fn (self $action) => [$action->value => __("app.actions.{$action->value}")])
            ->all();
    }
}
