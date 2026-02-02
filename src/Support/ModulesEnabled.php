<?php

declare(strict_types=1);

namespace Victormgomes\LaravelModules\Support;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\SplFileInfo;

class ModulesEnabled
{
    /**
     * Get the list of enabled module names from config.
     */
    public static function getList(): array
    {
        $enabledModules = config('modules.modules_enabled', '');

        if (is_array($enabledModules)) {
            return array_filter($enabledModules);
        }

        $modules = explode(',', (string) $enabledModules);

        return array_filter(array_map('trim', $modules));
    }

    /**
     * Retrieve all valid Seeder classes from enabled modules.
     */
    public static function getSeeders(): array
    {
        $seeders = [];

        foreach (self::getList() as $moduleName) {
            $seeders = array_merge($seeders, self::getSeedersForModule($moduleName));
        }

        return $seeders;
    }

    /**
     * Retrieve all migration file paths from enabled modules, sorted.
     */
    public static function getMigrations(): array
    {
        $migrations = [];

        foreach (self::getList() as $moduleName) {
            $migrations = array_merge($migrations, self::getMigrationsForModule($moduleName));
        }

        sort($migrations);

        return $migrations;
    }

    public static function getTenantMigrations(): array
    {
        $migrations = [];

        foreach (self::getList() as $moduleName) {
            $migrations = array_merge($migrations, self::getTenantMigrationsForModule($moduleName));
        }

        sort($migrations);

        return $migrations;
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Helper Methods
    |--------------------------------------------------------------------------
    */

    protected static function getSeedersForModule(string $moduleName): array
    {
        $path = base_path("modules/{$moduleName}/Database/Seeders");

        if (! File::isDirectory($path)) {
            return [];
        }

        Log::info("Scanning seeders in module: {$moduleName}");

        return collect(File::files($path))
            ->map(fn (SplFileInfo $file) => self::resolveSeederClass($moduleName, $file))
            ->filter()
            ->values()
            ->all();
    }

    protected static function resolveSeederClass(string $moduleName, SplFileInfo $file): ?string
    {
        $className = $file->getBasename('.php');
        $fqcn = "Modules\\{$moduleName}\\Database\\Seeders\\{$className}";

        if (class_exists($fqcn) && is_subclass_of($fqcn, Seeder::class)) {
            return $fqcn;
        }

        return null;
    }

    protected static function getMigrationsForModule(string $moduleName): array
    {
        $moduleDef = new ModuleDefinition($moduleName);
        $migrationPath = "{$moduleDef->path}/Database/Migrations";

        if (! is_dir($migrationPath)) {
            return [];
        }

        return glob($migrationPath.'/*.php') ?: [];
    }

    protected static function getTenantMigrationsForModule(string $moduleName): array
    {
        $moduleDef = new ModuleDefinition($moduleName);
        $migrationPath = "{$moduleDef->path}/Database/Migrations/Tenant";

        if (! is_dir($migrationPath)) {
            return [];
        }

        return glob($migrationPath.'/*.php') ?: [];
    }
}
