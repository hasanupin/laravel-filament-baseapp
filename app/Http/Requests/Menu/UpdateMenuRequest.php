<?php

namespace App\Http\Requests\Menu;

use Filament\Support\Icons\Heroicon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function rules(): array
    {
        return static::rulesFor();
    }

    /**
     * Shared by HTTP requests and the Filament modal form (Livewire has no FormRequest).
     *
     * @return array<string, list<mixed>>
     */
    public static function rulesFor(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:50'],
            'icon' => ['required', Rule::enum(Heroicon::class)],
            'group' => ['nullable', Rule::in(array_keys(__('app.menu_groups')))],
            'sort' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
