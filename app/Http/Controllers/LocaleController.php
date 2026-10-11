<?php

namespace App\Http\Controllers;

use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(string $locale, LocaleService $locales): RedirectResponse
    {
        $locales->switchTo($locale);

        return redirect()->back();
    }
}
