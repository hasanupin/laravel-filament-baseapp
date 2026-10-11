<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public function update(Menu $menu, array $data): Menu
    {
        // key is the link to the module in code; never editable.
        unset($data['key']);

        $menu->update($data);

        return $menu;
    }

    /**
     * @param  list<int|string>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                Menu::whereKey($id)->update(['sort' => $index + 1]);
            }
        });
    }
}
