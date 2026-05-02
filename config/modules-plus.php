<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Stubs
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will automatically use its internal optimized
    | stubs for creating new modules if you haven't published them yet.
    |
    */
    'custom_stubs' => true,

    /*
    |--------------------------------------------------------------------------
    | Additional Generator Paths
    |--------------------------------------------------------------------------
    |
    | These paths are used for resource discovery when they are not defined
    | in the main nwidart/laravel-modules configuration.
    |
    */
    'paths' => [
        'livewire' => ['path' => 'Livewire', 'generate' => true],
    ],
];
