<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Support;

class MigrationPaths
{
    /**
     * Get migration paths for central or tenant.
     *
     * @param  string  $type  'central' or 'tenant'
     */
    public static function get(string $type = 'central'): array
    {
        $paths = [];
        $basePath = database_path('migrations');
        $modulesPath = base_path('modules');
        $enabledModules = self::getEnabledModules();

        if ($type === 'central') {
            // Central database loads BOTH central and tenant migrations
            // 1. Base database/migrations
            $paths[] = realpath($basePath);

            $centralPath = $basePath.DIRECTORY_SEPARATOR.'central';
            if (is_dir($centralPath)) {
                $paths[] = realpath($centralPath);
            }

            $tenantPath = $basePath.DIRECTORY_SEPARATOR.'tenant';
            if (is_dir($tenantPath)) {
                $paths[] = realpath($tenantPath);
            }

            // 2. Modules
            foreach ($enabledModules as $moduleName) {
                $moduleMigrationPath = $modulesPath.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations';

                if (! is_dir($moduleMigrationPath)) {
                    continue;
                }

                // Load root of module migrations
                $paths[] = realpath($moduleMigrationPath);

                // Load Central subfolder
                $path = $moduleMigrationPath.DIRECTORY_SEPARATOR.'Central';
                if (is_dir($path)) {
                    $paths[] = realpath($path);
                }

                // Load Tenant subfolder (for Central DB as well)
                $path = $moduleMigrationPath.DIRECTORY_SEPARATOR.'Tenant';
                if (is_dir($path)) {
                    $paths[] = realpath($path);
                }
            }
        } else {
            // Tenant database loads ONLY tenant migrations
            // 1. Base database/migrations
            $tenantPath = $basePath.DIRECTORY_SEPARATOR.'tenant';
            if (is_dir($tenantPath)) {
                $paths[] = realpath($tenantPath);
            }

            // 2. Modules
            foreach ($enabledModules as $moduleName) {
                $moduleMigrationPath = $modulesPath.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations';

                if (! is_dir($moduleMigrationPath)) {
                    continue;
                }

                $path = $moduleMigrationPath.DIRECTORY_SEPARATOR.'Tenant';
                if (is_dir($path)) {
                    $paths[] = realpath($path);
                }
            }
        }

        return array_unique(array_filter($paths));
    }

    protected static function getEnabledModules(): array
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return [];
        }

        $content = file_get_contents($envPath);
        if ($content === false) {
            return [];
        }

        if (preg_match('/^APP_MODULES_ENABLED=(.*)$/m', $content, $matches)) {
            $value = trim($matches[1]);

            return $value ? array_map('trim', explode(',', $value)) : [];
        }

        return [];
    }
}
