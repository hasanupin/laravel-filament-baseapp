<?php

namespace Database\Seeders;

use App\Enums\Module;
use App\Models\Menu;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $icons = [
            Module::Users->value => Heroicon::OutlinedUsers,
            Module::Roles->value => Heroicon::OutlinedShieldCheck,
            Module::Menus->value => Heroicon::OutlinedBars3,
        ];

        foreach (Module::cases() as $sort => $module) {
            // firstOrCreate: re-seeding must not undo labels an admin renamed.
            Menu::firstOrCreate(['key' => $module->value], [
                'label' => null, // translated default, see Menu::displayLabel()
                'icon' => ($icons[$module->value] ?? Heroicon::OutlinedSquares2x2)->value,
                'group' => 'settings', // translation key, see Menu::displayGroup()
                'sort' => $sort + 1,
                'is_active' => true,
            ]);
        }
    }
}
