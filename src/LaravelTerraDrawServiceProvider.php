<?php

namespace DevRajThapa\LaravelTerraDraw;

use DevRajThapa\LaravelTerraDraw\Commands\LaravelTerraDrawCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTerraDrawServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-terra-draw')
            ->hasConfigFile('terra-draw');
        // ->hasViews()
        // ->hasCommand(LaravelTerraDrawCommand::class);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(TerraDraw::class, function ($app) {
            return new TerraDraw(config('terra-draw', []));
        });
    }
}
