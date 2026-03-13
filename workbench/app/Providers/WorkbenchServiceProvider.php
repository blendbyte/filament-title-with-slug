<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        config()->set('app.name', 'Title With Slug Workbench');
        config()->set('cache.default', 'array');
        config()->set('session.driver', 'file');
    }
}
