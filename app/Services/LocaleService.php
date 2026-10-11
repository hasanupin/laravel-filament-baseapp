<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class LocaleService
{
    /** First entry is the default. */
    public const SUPPORTED = ['id', 'en'];

    public function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    public function switchTo(string $locale): void
    {
        abort_unless($this->isSupported($locale), 404);

        session(['locale' => $locale]);
        App::setLocale($locale);
    }

    /**
     * Apply the visitor's chosen language for this request (session only; not stored per user).
     */
    public function applyFromSession(): void
    {
        $locale = session('locale');

        if (is_string($locale) && $this->isSupported($locale)) {
            App::setLocale($locale);
        }
    }

    /**
     * @return array<string, string> locale => its own name, e.g. ['id' => 'Bahasa Indonesia']
     */
    public function options(): array
    {
        return collect(self::SUPPORTED)->mapWithKeys(fn ($locale) => [$locale => __("app.locale.{$locale}")])->all();
    }
}
