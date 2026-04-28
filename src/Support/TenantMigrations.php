<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Support;

class TenantMigrations
{
    public static function getMigrations(): array
    {
        return MigrationPaths::get('tenant');
    }
}
