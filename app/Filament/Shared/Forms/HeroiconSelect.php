<?php

namespace App\Filament\Shared\Forms;

use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * Searchable picker of outlined Heroicons (one visual weight in the sidebar), showing each icon next to its name.
 */
class HeroiconSelect
{
    public static function make(string $name = 'icon'): Select
    {
        return Select::make($name)
            ->searchable()
            ->options(static::options())
            ->allowHtml();
    }

    /**
     * @return array<string, string> heroicon value => HTML preview
     */
    public static function options(): array
    {
        return once(fn () => collect(Heroicon::cases())
            ->filter(fn (Heroicon $icon) => str_starts_with($icon->value, 'o-'))
            ->mapWithKeys(fn (Heroicon $icon) => [
                // Inline style: app Tailwind classes aren't in Filament's compiled CSS.
                $icon->value => '<span style="display:inline-flex;align-items:center;gap:.5rem">'
                    .svg('heroicon-'.$icon->value, '', ['style' => 'width:1.25rem;height:1.25rem;flex-shrink:0'])->toHtml()
                    .e(Str::of($icon->value)->after('o-')->replace('-', ' '))
                    .'</span>',
            ])
            ->all());
    }
}
