<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PermissionAction: string implements HasLabel
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
            ->mapWithKeys(fn (self $action) => [$action->value => $action->getLabel()])
            ->all();
    }

    public function getLabel(): string
    {
        return __("app.actions.{$this->value}");
    }
}
