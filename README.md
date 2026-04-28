# Laravel Modules Plus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)
[![License](https://img.shields.io/packagist/l/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)

**Advanced infrastructure and zero-config resource discovery addon for `nwidart/laravel-modules`.**

---

## Introduction

**Laravel Modules Plus** is a powerful, zero-configuration addon designed to transform your modules into truly self-contained, portable packages. It automates the "heavy lifting" of resource registration and provides robust environment-level control, ensuring your modular architecture is enterprise-ready.

## Key Features

*   **`.env` Based Activation**: Control enabled modules directly via your environment file (`APP_MODULES_ENABLED`). No more tracking `modules_statuses.json` in version control.
*   **Abstract Base Provider**: Automatically discovers and registers:
    *   **Routes**: API, Web, Console, and Broadcast Channels.
    *   **Auto-Discovery**: Policies, Observers, and Events based on folder naming conventions.
    *   **Components**: Blade and Livewire components registered automatically.
    *   **Resources**: Zero-config loading of Views and Translations.
*   **Smart Multi-Tenancy Migrations**: Intelligent separation of Central vs. Tenant migrations.
*   **Modern Autoloading**: Built-in stubs optimized for `wikimedia/composer-merge-plugin`.
*   **Portable Stubs**: Includes internal optimized stubs that ensure architectural consistency without manual setup.

---

## Installation

1. Install the package via composer:
```bash
composer require victormgomes/laravel-modules-plus
```

2. (Optional) Publish the configuration and stubs:
```bash
php artisan modules-plus:install
```

3. Update your `config/modules.php` to use the new Activator:
```php
'activator' => 'env',
'activators' => [
    'env' => [
        'class' => \Victormgomes\ModulesPlus\Activators\EnvActivator::class,
    ],
],
```

---

## Usage

### 1. Activating Modules
Modules are managed via your `.env` file. Only modules listed here will be booted:

```env
APP_MODULES_ENABLED=Auth,User,Chat,Billing
```

You can also use standard commands which will automatically update your `.env`:
```bash
php artisan module:enable Chat
```

### 2. Creating New Modules
When you create a module, it will automatically use the optimized stubs (if published or if `custom_stubs` is enabled in config). 

The generated Service Provider will extend `AbstractModuleServiceProvider`. This parent class handles all registration automatically as long as you follow the standard folder structure:

*   `Routes/api.php`, `Routes/web.php` -> Loaded automatically.
*   `Policies/` -> `UserPolicy` automatically linked to `Models/User`.
*   `Observers/` -> `UserObserver` automatically linked to `Models/User`.
*   `Listeners/` -> Events discovered via `DiscoverEvents`.
*   `Database/Migrations/Tenant` -> Automatically loaded only for tenant database contexts.

### 3. Modern Autoloading
To keep modules decoupled, this package assumes each module has its own `composer.json`. After creating a new module, simply run:
```bash
composer dump-autoload
```

---

## Testing

```bash
composer test
```

## Credits

- [Victor M. Gomes](https://github.com/VictorMGomes)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
