<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Support;

use Illuminate\Support\Facades\File;

class SeederPaths
{
    /**
     * Get all seeder classes for a specific type (e.g., 'Tenant').
     *
     * @param  string  $type  The subfolder to look for (e.g., 'Tenant')
     * @return array<string>
     */
    public static function get(string $type = 'Tenant'): array
    {
        $seeders = [];
        $modulesPath = base_path('modules');
        $enabledModules = self::getEnabledModules();

        foreach ($enabledModules as $moduleName) {
            $seederPath = $modulesPath . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Seeders' . DIRECTORY_SEPARATOR . $type;

            if (File::isDirectory($seederPath)) {
                $files = File::files($seederPath);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Seeder.php')) {
                        $className = "Modules\\{$moduleName}\\Database\\Seeders\\{$type}\\" . $file->getBasename('.php');
                        if (class_exists($className)) {
                            $seeders[] = $className;
                        }
                    }
                }
            }
        }

        return $seeders;
    }

    protected static function getEnabledModules(): array
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return [];
        }

        $content = File::get($envPath);
        if (preg_match('/^APP_MODULES_ENABLED=(.*)$/m', $content, $matches)) {
            $value = trim($matches[1]);
            return $value ? array_map('trim', explode(',', $value)) : [];
        }

        return [];
    }
}
