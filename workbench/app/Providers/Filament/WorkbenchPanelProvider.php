<?php

namespace Workbench\App\Providers\Filament;

use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Filament\Pages\TitleWithSlugPlayground;
use Workbench\App\Http\Middleware\UseRequestHostForAssets;
use function Orchestra\Testbench\package_path;

class WorkbenchPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentAsset::register([
            Theme::make('workbench-theme', package_path('workbench/resources/css/filament/admin/theme.css'))
                ->relativePublicPath('css/filament/admin/theme.css'),
        ], 'app');

        return $panel
            ->id('admin')
            ->path('admin')
            ->brandName('Title With Slug')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->theme('workbench-theme')
            ->pages([
                TitleWithSlugPlayground::class,
            ])
            ->middleware([
                UseRequestHostForAssets::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);
    }
}
