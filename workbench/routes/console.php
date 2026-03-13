<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use function Orchestra\Testbench\package_path;

Artisan::command('workbench:filament-theme', function () {
    $template = package_path('workbench/resources/css/filament/admin/theme.css');
    $packageStylesheet = package_path('resources/dist/filament-title-with-slug.css');
    $output = public_path('css/filament/admin/theme.css');

    if (! is_file($template)) {
        $this->error("Theme template [{$template}] not found.");

        return 1;
    }

    if (! is_file($packageStylesheet)) {
        $this->error("Package stylesheet [{$packageStylesheet}] not found.");

        return 1;
    }

    File::ensureDirectoryExists(dirname($output));

    File::put(
        $output,
        rtrim(File::get($template)) . PHP_EOL . PHP_EOL . File::get($packageStylesheet) . PHP_EOL,
    );

    $this->info("Published workbench theme [{$output}].");

    return 0;
})->purpose('Build the custom Filament theme used by the workbench.');
