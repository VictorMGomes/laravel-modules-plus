<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Support;

class TenantSeeders
{
    /**
     * Get all tenant-specific seeder classes.
     *
     * @return array<string>
     */
    public static function getSeeders(): array
    {
        return SeederPaths::get('Tenant');
    }
}
