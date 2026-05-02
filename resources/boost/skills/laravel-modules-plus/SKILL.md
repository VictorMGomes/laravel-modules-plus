---
name: laravel-modules-plus
description: Expert knowledge for victormgomes/laravel-modules-plus — a zero-config addon for nWidart/laravel-modules v13 that adds environment-driven module activation (EnvActivator), auto-discovery of routes/policies/observers/events via AbstractModuleServiceProvider, modular factory resolution, and dynamic contextual seeding.
compatible_agents:
    - Claude Code
    - Cursor
    - GitHub Copilot
    - Gemini
    - Windsurf
    - Cline
    - Codex
    - Roo
    - VSCode
tags:
    - laravel
    - php
    - modules
    - modular
    - nwidart
    - multi-tenancy
    - service-provider
    - factories
    - seeders
---

# Laravel Modules Plus

## Context

You are working with **`victormgomes/laravel-modules-plus`** (v0.0.9+), a zero-configuration engine built on top of **`nwidart/laravel-modules` v13**. It transforms modules into autonomous, plug-and-play components with:

- **EnvActivator**: Module activation via `.env` (`APP_MODULES_ENABLED`) — fully integrated with standard Artisan commands.
- **AbstractModuleServiceProvider**: Base provider with dynamic discovery for routes, config, views, translations, policies, observers, and events.
- **Modular Factory Resolution**: Standard Laravel `HasFactory` works out-of-the-box for modular models without manual linking.
- **Dynamic Contextual Seeding**: Support for discovering and running seeders in subfolders (e.g., `database/seeders/Tenant/`).
- **Convention-Driven**: Dynamic path resolution that respects any custom structure defined in your `modules.php` configuration.

**Requirements:**

- PHP ^8.4
- Laravel ^11.0 || ^12.0 || ^13.0
- `nwidart/laravel-modules` ^13.0

**Package namespace:** `Victormgomes\ModulesPlus`

---

## Installation & Setup

```bash
# 1. Install via composer
composer require victormgomes/laravel-modules-plus

# 2. Publish config and optimized stubs
php artisan modules-plus:install
```

**Required `config/modules.php` setup:**

```php
'activator' => 'env',
'activators' => [
    'env' => [
        'class' => \Victormgomes\ModulesPlus\Activators\EnvActivator::class,
    ],
],
```

---

## Rules & Best Practices

### Plug-and-Play Architecture

- **ALWAYS** extend `\Victormgomes\ModulesPlus\Providers\AbstractModuleServiceProvider` for your module providers.
- **NEVER** manually call `$this->loadRoutesFrom()`, `Gate::policy()`, or `Model::observe()` — discovery is handled automatically.
- **NEVER** use `protected static function newFactory()` in models unless overriding the modular convention.

### Module Activation

- List active modules in `.env`: `APP_MODULES_ENABLED=Auth,User,Chat`.
- Standard commands like `php artisan module:enable {name}` will automatically update your `.env`.

### Standard Folder Conventions

The package respects your `modules.php` settings but defaults to:

```
Modules/Auth/
├── app/
│   ├── Models/           ← Use standard HasFactory
│   ├── Policies/         ← e.g. UserPolicy.php (auto-linked to Models/User.php)
│   ├── Observers/        ← e.g. UserObserver.php (auto-linked to Models/User.php)
│   ├── Listeners/        ← Auto-discovered for events
│   ├── Console/Commands/ ← Auto-registered
│   └── Providers/        ← Extends AbstractModuleServiceProvider
├── database/
│   ├── factories/        ← Resolved automatically
│   ├── migrations/       ← Central context
│   │   └── Tenant/       ← Context-specific (e.g. Tenant)
│   └── seeders/          ← Generic context
│       └── Tenant/       ← Context-specific (e.g. Tenant)
├── routes/
│   ├── api.php           ← Loaded automatically
│   └── web.php           ← Loaded automatically
└── config/auth.php       ← Merged automatically
```

### Contextual Seeding

Use the provided helpers to discover seeders in specific subfolders:

```php
use Victormgomes\ModulesPlus\Support\TenantSeeders;
use Victormgomes\ModulesPlus\Support\SeederPaths;

// In your main Tenant seeder:
$this->call(TenantSeeders::getSeeders());

// For custom contexts:
$this->call(SeederPaths::get('Sample'));
```

## Anti-Patterns

- **Double Registration**: Manually loading resources that are already covered by auto-discovery.
- **Hardcoded Paths**: Using string paths instead of the `ModularPath` helper when extending package logic.
- **Naming Mismatches**: Policy/Observer names not matching the model prefix (e.g., `AdministratorPolicy` instead of `UserPolicy` for `User.php`).
- **Ignoring context**: Placing tenant-specific migrations in the root `migrations/` folder.
