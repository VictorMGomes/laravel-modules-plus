<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Support;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use RuntimeException;

class ModularPath
{
    /**
     * Get the standardized path for a given resource type.
     *
     * @param  string  $key  The generator path key
     */
    public static function get(string $key): string
    {
        $config = config("modules.paths.generator.{$key}");

        if ($config === null) {
            $config = config("modules-plus.paths.{$key}");
        }

        if ($config === null) {
            throw new RuntimeException("Modular path key [{$key}] not found in 'modules.paths.generator' or 'modules-plus.paths'. Please ensure it is defined in your configuration files.");
        }

        $path = is_array($config) ? ($config['path'] ?? null) : $config;

        if ($path === null) {
            throw new RuntimeException("The configuration for modular path [{$key}] is missing the 'path' attribute.");
        }

        return (string) Str::replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Get the standardized namespace for a given resource type.
     *
     * @param  string  $key  The generator path key
     */
    public static function getNamespace(string $key): string
    {
        if (config("modules.paths.generator.{$key}") !== null) {
            return GenerateConfigReader::read($key)->getNamespace();
        }

        $config = config("modules-plus.paths.{$key}");

        if ($config === null) {
            throw new RuntimeException("Modular path key [{$key}] not found for namespace resolution.");
        }

        // Simple namespace resolution for custom paths if not using nwidart generator logic
        $path = is_array($config) ? ($config['path'] ?? '') : $config;

        return (string) Str::of($path)
            ->replace(['/', '\\'], '\\')
            ->trim('\\')
            ->explode('\\')
            ->map(fn ($segment) => Str::studly($segment))
            ->implode('\\');
    }
}
