<?php

namespace Camya\Filament;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTitleWithSlugServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-title-with-slug')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }
}
