<?php

namespace App\Filament\Shared\Concerns;

use App\Models\Menu;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * Sidebar label/icon/group/order/visibility come from the `menus` row for static::$module (edited under Settings → User Menu).
 * Falls back to the resource's own defaults when the row doesn't exist.
 */
trait HasManagedNavigation
{
    protected static function menu(): ?Menu
    {
        return Menu::forModule(static::$module);
    }

    public static function getNavigationLabel(): string
    {
        return static::menu()?->displayLabel() ?? static::$module->getLabel();
    }

    // Page title and breadcrumb follow the sidebar label, so renaming a menu item renames its page.
    public static function getPluralModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return Heroicon::tryFrom((string) static::menu()?->icon) ?? parent::getNavigationIcon();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::menu() ? static::menu()->displayGroup() : __('app.menu_groups.settings');
    }

    public static function getNavigationSort(): ?int
    {
        return static::menu()?->sort ?? parent::getNavigationSort();
    }

    // Hiding a menu item is cosmetic; page access is still decided by the role's policy.
    public static function shouldRegisterNavigation(): bool
    {
        return (static::menu()?->is_active ?? true) && parent::shouldRegisterNavigation();
    }
}
