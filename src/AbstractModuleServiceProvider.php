<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;
use Victormgomes\ModulesPlus\Support\ModularPath;

abstract class AbstractModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName;

    protected string $modulePath;

    protected string $moduleNamespace;

    /**
     * @var array<string, string>
     */
    protected array $morphMaps = [];

    public function __construct($app)
    {
        parent::__construct($app);

        $reflect = new ReflectionClass($this);
        $fileName = $reflect->getFileName();

        if ($fileName === false) {
            throw new RuntimeException('Unable to determine module path from reflection');
        }

        $this->modulePath = dirname($fileName, 2);

        $this->moduleNamespace = $reflect->getNamespaceName();

        $this->moduleNamespace = Str::beforeLast($this->moduleNamespace, '\\');

        if (! isset($this->moduleName)) {
            $this->moduleName = class_basename($this->moduleNamespace);
        }
    }

    protected function path(string ...$segments): string
    {
        return $this->modulePath.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $segments);
    }

    public function boot(): void
    {
        $this->bootConfig();
        $this->bootViews();
        $this->bootTranslations();
        $this->bootMigrations();
        $this->bootRoutes();

        $this->bootCommands();
        $this->bootComponents();
        $this->bootMorphMap();
        $this->bootSchedule();
        $this->bootLivewire();
        $this->bootAbout();

        $this->discoverPolicies();
        $this->discoverEvents();
        $this->discoverObservers();

        $this->registerMiddleware();

        $this->bootAssets();
    }

    protected function registerMiddleware(): void
    {
        // To be implemented by child modules
        // e.g. $this->app['router']->aliasMiddleware('name', Middleware::class);
    }

    public function register(): void
    {
        $this->registerBindings();
    }

    protected function discoverPolicies(): void
    {
        $policiesPath = $this->path(ModularPath::get('policies'));

        if (! is_dir($policiesPath)) {
            return;
        }

        foreach ((new Finder)->in($policiesPath)->files() as $policyFile) {
            $policyClass = $this->getClassFromFile($policyFile);

            if (! $policyClass) {
                continue;
            }

            $modelName = class_basename($policyClass);
            $modelName = Str::replaceLast('AccessPolicy', '', $modelName);
            $modelName = Str::replaceLast('Policy', '', $modelName);

            $modelClass = "{$this->moduleNamespace}\\" . ModularPath::getNamespace('model') . "\\{$modelName}";

            if (class_exists($modelClass) && class_exists($policyClass)) {
                Gate::policy($modelClass, $policyClass);
            }
        }
    }

    protected function discoverEvents(): void
    {
        $listenersPath = $this->path(ModularPath::get('listener'));

        if (! is_dir($listenersPath)) {
            return;
        }

        $events = DiscoverEvents::within($listenersPath, base_path());

        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    protected function discoverObservers(): void
    {
        $observersPath = $this->path(ModularPath::get('observer'));

        if (! is_dir($observersPath)) {
            return;
        }

        foreach ((new Finder)->in($observersPath)->files() as $observerFile) {
            $observerClass = $this->getClassFromFile($observerFile);

            if (! $observerClass) {
                continue;
            }

            $modelName = class_basename($observerClass);

            $possibleSuffixes = ['AuditObserver', 'Observer'];
            foreach ($possibleSuffixes as $suffix) {
                if (str_ends_with($modelName, $suffix)) {
                    $modelName = substr($modelName, 0, -strlen($suffix));
                    break;
                }
            }

            $modelClass = "{$this->moduleNamespace}\\" . ModularPath::getNamespace('model') . "\\{$modelName}";

            if (class_exists($modelClass) && class_exists($observerClass)) {
                $modelClass::observe($observerClass);
            }
        }
    }

    protected function bootConfig(): void
    {
        $fileName = strtolower($this->moduleName).'.php';
        $configPath = $this->path(ModularPath::get('config'), $fileName);

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, strtolower($this->moduleName));
        }
    }

    protected function bootViews(): void
    {
        $viewPath = $this->path(ModularPath::get('views'));
        if (is_dir($viewPath)) {
            $this->loadViewsFrom($viewPath, strtolower($this->moduleName));
        }
    }

    protected function bootTranslations(): void
    {
        $langPath = $this->path(ModularPath::get('lang'));
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($this->moduleName));
            $this->loadJsonTranslationsFrom($langPath);
        }
    }

    protected function bootMigrations(): void
    {
        $path = $this->path(ModularPath::get('migration'));
        if (is_dir($path)) {
            // Only load the root of the Migrations directory (Central)
            // and explicitly EXCLUDE the Tenant subfolder if it exists.
            $this->loadMigrationsFrom($path);
        }
    }

    protected function bootRoutes(): void
    {
        $routesPath = $this->path(ModularPath::get('routes'));

        if (! is_dir($routesPath)) {
            return;
        }

        if (file_exists($routesPath.'/web.php')) {
            Route::middleware('web')
                ->group($routesPath.'/web.php');
        }

        if (file_exists($routesPath.'/api.php')) {
            Route::middleware('api')
                ->group($routesPath.'/api.php');
        }

        if (file_exists($routesPath.'/console.php')) {
            $this->loadRoutesFrom($routesPath.'/console.php');
        }

        if (file_exists($routesPath.'/channels.php')) {
            require $routesPath.'/channels.php';
        }
    }

    protected function bootCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $commandsPath = $this->path(ModularPath::get('command'));
        if (! is_dir($commandsPath)) {
            return;
        }

        foreach ((new Finder)->in($commandsPath)->files() as $file) {
            $class = $this->getClassFromFile($file);
            if ($class && class_exists($class) && ! (new ReflectionClass($class))->isAbstract()) {
                $this->commands([$class]);
            }
        }
    }

    protected function bootComponents(): void
    {
        $namespace = "{$this->moduleNamespace}\\" . ModularPath::getNamespace('component-class');
        Blade::componentNamespace($namespace, strtolower($this->moduleName));
    }

    protected function bootMorphMap(): void
    {
        $maps = $this->morphMaps;

        $modelClass = "{$this->moduleNamespace}\\" . ModularPath::getNamespace('model') . "\\{$this->moduleName}";
        if (class_exists($modelClass)) {
            $maps[strtolower($this->moduleName)] = $modelClass;
        }

        if (! empty($maps)) {
            Relation::enforceMorphMap($maps);
        }
    }

    protected function bootAssets(): void
    {
        $sourcePath = $this->path(ModularPath::get('assets'));
        if (is_dir($sourcePath)) {
            $moduleKey = strtolower($this->moduleName);
            $this->publishes([$sourcePath => public_path("vendor/{$moduleKey}")], ["{$moduleKey}-assets"]);
        }
    }

    protected function bootSchedule(): void
    {
        if (method_exists($this, 'schedule')) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $this->schedule($schedule);
            });
        }
    }

    protected function bootLivewire(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        $livewirePath = $this->path(ModularPath::get('livewire'));
        if (! is_dir($livewirePath)) {
            return;
        }

        $prefix = strtolower($this->moduleName);
        foreach ((new Finder)->in($livewirePath)->files() as $file) {
            $class = $this->getClassFromFile($file);
            if ($class && ! (new ReflectionClass($class))->isAbstract()) {
                $alias = $prefix.'::'.Str::kebab(class_basename($class));
                \Livewire\Livewire::component($alias, $class);
            }
        }
    }

    protected function bootAbout(): void
    {
        if (class_exists(AboutCommand::class)) {
            AboutCommand::add('Modules', fn () => [$this->moduleName => 'Enabled']);
        }
    }

    protected function registerBindings(): void {}

    protected function getClassFromFile(SplFileInfo|string $file): ?string
    {
        $filePath = $file instanceof SplFileInfo ? $file->getPathname() : $file;

        try {
            $contents = file_get_contents($filePath);
            if ($contents === false) {
                return null;
            }

            $namespace = null;
            $class = null;

            if (preg_match('/^namespace\s+(.+?);/m', $contents, $matches)) {
                $namespace = $matches[1];
            }

            if (preg_match('/^class\s+(\w+)/m', $contents, $matches)) {
                $class = $matches[1];
            }

            if ($class) {
                return $namespace ? "$namespace\\$class" : $class;
            }

            return null;
        } catch (Throwable) {
            return null;
        }
    }
}
