<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Services\MenuService;
use Filament\Resources\Pages\ManageRecords;

class ManageMenus extends ManageRecords
{
    protected static string $resource = MenuResource::class;

    // No create: menu rows are seeded per module.
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        abort_unless(auth()->user()->can('reorder', static::getResource()::getModel()), 403);

        app(MenuService::class)->reorder($order);
    }
}
