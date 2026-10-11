<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\EditProfile;
use App\Filament\Admin\Pages\Auth\Login;
use App\Http\Middleware\SetLocale;
use App\Services\LocaleService;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->brandName(fn () => config('app.name'))
            ->login(Login::class)
            ->passwordReset()
            // Avatar menu → profile (name, password, email). New email applies only after its link is clicked.
            ->profile(EditProfile::class, isSimple: false)
            ->emailChangeVerification()
            // Neutral zinc base, one teal accent (buttons, active nav, focus). No DESIGN.md yet, so this is the brand.
            ->colors([
                'primary' => Color::Teal,
                'gray' => Color::Zinc,
            ])
            // System font: no web-font download for an internal tool.
            ->font('ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif', provider: LocalFontProvider::class)
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems(array_map(
                fn (string $locale) => Action::make("locale_{$locale}")
                    ->label(fn () => __("app.locale.{$locale}"))
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('locale.switch', $locale))
                    ->visible(fn () => app()->getLocale() !== $locale),
                LocaleService::SUPPORTED,
            ))
            ->renderHook(PanelsRenderHook::SIMPLE_PAGE_END, fn () => Blade::render(
                '<p style="text-align:center;margin-top:1.5rem;font-size:.875rem">@foreach ($options as $locale => $name)@if ($locale !== app()->getLocale())<x-filament::link :href="route(\'locale.switch\', $locale)">{{ $name }}</x-filament::link>@endif @endforeach</p>',
                ['options' => app(LocaleService::class)->options()],
            ))
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
