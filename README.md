# Laravel Modules Plus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/laravel-modules-plus/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/victormgomes/laravel-modules-plus/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/laravel-modules-plus/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/victormgomes/laravel-modules-plus/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)
[![License](https://img.shields.io/packagist/l/victormgomes/laravel-modules-plus.svg?style=flat-square)](https://packagist.org/packages/victormgomes/laravel-modules-plus)

**Enhancements for the nWidart/laravel-modules package**

---

## Introduction

**Laravel Modules Plus** is a powerful, zero-configuration addon designed to transform your modules into truly self-contained, portable packages. It automates the "heavy lifting" of resource registration and provides robust environment-level control, ensuring your modular architecture is enterprise-ready.

### Why use this package?

*   **Zero-Config Discovery**: Automatically discovers and registers routes, policies, observers, and events based on simple folder conventions.
*   **Environment Control**: Manage module activation via `.env` (`APP_MODULES_ENABLED`), eliminating the need to track `modules_statuses.json` in version control.
*   **Multi-Tenancy Ready**: Intelligent separation of Central and Tenant migrations for complex application architectures.
*   **Portability**: Optimized stubs ensure that every new module follows a consistent, decoupled structure from day one.

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
Modules are managed via your `.env` file. Only modules listed here will be booted:

```env
APP_MODULES_ENABLED=Auth,User,Chat,Billing
```

### 2. Creating New Modules
The generated Service Provider will extend `AbstractModuleServiceProvider`. This parent class handles all registration automatically as long as you follow the standard folder structure:

*   `Routes/api.php`, `Routes/web.php` -> Loaded automatically.
*   `Policies/` -> `UserPolicy` automatically linked to `Models/User`.
*   `Observers/` -> `UserObserver` automatically linked to `Models/User`.
*   `Database/Migrations/Tenant` -> Automatically loaded only for tenant database contexts.

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
