<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Activators;

use Illuminate\Support\Facades\File;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

class EnvActivator implements ActivatorInterface
{
    protected string $envPath;

    public function __construct()
    {
        $this->envPath = base_path('.env');
    }

    public function enable(Module $module): void
    {
        $this->setActiveByName($module->getName(), true);
    }

    public function disable(Module $module): void
    {
        $this->setActiveByName($module->getName(), false);
    }

    public function hasStatus(Module|string $module, bool $status): bool
    {
        $moduleName = $module instanceof Module ? $module->getName() : $module;
        $modules = $this->getModules();

        return in_array($moduleName, $modules) === $status;
    }

    public function setActive(Module $module, bool $active): void
    {
        $this->setActiveByName($module->getName(), $active);
    }

    public function setActiveByName(string $name, bool $active): void
    {
        $modules = $this->getModules();
        $exists = in_array($name, $modules);

        if ($active && $exists) {
            return;
        }
        if (! $active && ! $exists) {
            return;
        }

        if ($active) {
            $modules[] = $name;
        } else {
            $modules = array_filter($modules, fn ($m) => $m !== $name);
        }

        $this->writeToEnv($modules);
    }

    public function delete(Module $module): void
    {
        $this->setActiveByName($module->getName(), false);
    }

    public function reset(): void
    {
        $this->writeToEnv([]);
    }

    protected function getModules(): array
    {
        if (! File::exists($this->envPath)) {
            return [];
        }

        $content = File::get($this->envPath);
        if (preg_match('/^APP_MODULES_ENABLED=(.*)$/m', $content, $matches)) {
            $value = trim($matches[1]);

            return $value ? array_map('trim', explode(',', $value)) : [];
        }

        return [];
    }

    protected function writeToEnv(array $modules): void
    {
        if (! File::exists($this->envPath)) {
            return;
        }

        $newValue = implode(',', $modules);
        $content = File::get($this->envPath);
        $pattern = '/^APP_MODULES_ENABLED=(.*)$/m';

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, "APP_MODULES_ENABLED={$newValue}", $content);
        } else {
            // Append with a newline if it doesn't end with one
            if (! str_ends_with($content, "\n")) {
                $content .= "\n";
            }
            $newContent = $content."APP_MODULES_ENABLED={$newValue}\n";
        }

        File::put($this->envPath, $newContent);
    }
}
