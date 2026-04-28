<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Commands;

use Illuminate\Console\Command;

class ModulesPlusCommand extends Command
{
    public $signature = 'laravel-modules-plus';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
