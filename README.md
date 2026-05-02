# Laravel Modules Plus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/laravel-modules-plus/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/victormgomes/laravel-modules-plus/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/laravel-modules-plus/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/victormgomes/laravel-modules-plus/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)
[![License](https://img.shields.io/packagist/l/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)

**The Plug-and-Play Engine for Laravel Modules.**

---

## Introduction

**Laravel Modules Plus** is a zero-configuration enhancement designed to transform your modules into truly self-contained, autonomous units. By extending the provided `AbstractModuleServiceProvider`, your modules instantly become **Plug-and-Play**: they automatically handle their own resource registration, factory resolution, and data seeding without any manual boilerplate in the main application.

### Why use this package?

*   **Module Autonomy**: Modules become self-configuring "mini-packages". Drop a module into any project, and its logic is instantly alive.
*   **Standardized Structure**: Enforces a clean, consistent convention across all modules, making your codebase predictable and enterprise-ready.
*   **Zero-Config Discovery**: Automatic registration of Routes, Policies, Observers, and Events based on simple folder conventions.
*   **Agnostic Factory Resolution**: Automatically resolves Eloquent factories for modular models. Keep your models clean and free of package-specific traits.
*   **Dynamic Seeding**: Effortlessly manage data population with helpers that discover seeders across your entire modular ecosystem.
*   **Contextual Flexibility**: Optionally separate resources (Migrations, Seeders) into specific contexts like `Tenant` or `Central` for complex architectures.

---

## Support us

We invest a lot of resources into creating [best in class open source packages](https://github.com/victormgomes). You can support us by [sponsoring us on GitHub](https://github.com/sponsors/VictorMGomes).

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
Manage module activation directly via your `.env` file, keeping your `modules_statuses.json` out of version control:

```env
APP_MODULES_ENABLED=Auth,User,Chat,Billing
```

### 2. Standard Plug-and-Play Structure
By extending `AbstractModuleServiceProvider`, the following resources are discovered and registered automatically:

*   **Routes**: `Routes/api.php` and `Routes/web.php` are loaded with standard middleware.
*   **Policies**: `Policies/UserPolicy.php` is automatically linked to `Models/User.php`.
*   **Observers**: `Observers/UserObserver.php` is automatically linked to `Models/User.php`.
*   **Factories**: Factories in `Database/Factories/` are automatically resolved for modular models using standard `HasFactory`.
*   **Migrations**: All migrations in `Database/Migrations/` are loaded by default.
*   **Seeders**: All seeders in `Database/Seeders/` can be dynamically retrieved for seeding.

### 3. Contextual Discovery (Advanced)
For more complex architectures (like Multi-Tenancy), the package allows you to silo resources into sub-contexts:

*   **Contextual Migrations**: Place migrations in `Database/Migrations/Tenant` to load them only in specific database contexts.
*   **Contextual Seeders**: Use subfolders like `Database/Seeders/Tenant/` and retrieve them using the `SeederPaths` helper:

```php
use Victormgomes\ModulesPlus\Support\SeederPaths;
use Victormgomes\ModulesPlus\Support\TenantSeeders;

// Get seeders for any custom context
$seeders = SeederPaths::get('MyContext');

// Or use the built-in Tenant helper
$seeders = TenantSeeders::getSeeders();

$this->call($seeders);
```

---

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Victor M. Gomes](https://github.com/VictorMGomes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
