<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Victormgomes\ModulesPlus\Commands\InstallCommand;

class ModulesPlusServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-modules-plus')
            ->hasConfigFile('modules-plus')
            ->hasCommand(InstallCommand::class);
    }

    public function packageBooted(): void
    {
        if (config('modules-plus.custom_stubs')) {
            $this->overrideStubPath();
        }

        // Register stubs for publishing
        $this->publishes([
            __DIR__.'/../stubs/nwidart-stubs' => base_path('stubs/nwidart-stubs'),
        ], 'modules-plus-stubs');
    }

    protected function overrideStubPath(): void
    {
        $projectStubsPath = base_path('stubs/nwidart-stubs');

        // Only override if the user hasn't published the stubs to the root folder
        if (! is_dir($projectStubsPath)) {
            if (class_exists(\Nwidart\Modules\Support\Stub::class)) {
                \Nwidart\Modules\Support\Stub::setBasePath(__DIR__.'/../stubs/nwidart-stubs');
            }
        }
    }
}
