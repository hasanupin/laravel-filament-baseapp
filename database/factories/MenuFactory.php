<?php

namespace Database\Factories;

use App\Enums\Module;
use App\Models\Menu;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->randomElement(Module::cases())->value,
            'label' => fake()->words(2, true),
            'icon' => Heroicon::OutlinedSquares2x2->value,
            'group' => 'settings',
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function forModule(Module $module): static
    {
        return $this->state(['key' => $module->value, 'label' => null]);
    }
}
