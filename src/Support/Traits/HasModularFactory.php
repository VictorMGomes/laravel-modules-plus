<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Support\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory as EloquentHasFactory;
use Illuminate\Support\Str;

trait HasModularFactory
{
    use EloquentHasFactory;

    /**
     * Get a new factory instance for the model.
     *
     * @param  mixed  $factory
     */
    public static function newFactory($factory = null): Factory
    {
        $modelName = static::class;

        if (Str::startsWith($modelName, 'Modules\\')) {
            $module = Str::after($modelName, 'Modules\\');
            $module = Str::before($module, '\\Models\\');

            $factoryClass = "Modules\\{$module}\\Database\\Factories\\".class_basename($modelName).'Factory';

            if (class_exists($factoryClass)) {
                return $factoryClass::new();
            }
        }

        return Factory::factoryForModel($modelName);
    }
}
