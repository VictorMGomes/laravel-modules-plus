<?php

declare(strict_types=1);

namespace Victormgomes\ModulesPlus\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'modules-plus:install';

    protected $description = 'Install the Laravel Modules Plus stubs and configuration';

    public function handle(): int
    {
        $this->info('Installing Laravel Modules Plus...');

        $this->call('vendor:publish', [
            '--tag' => 'modules-plus-config',
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'modules-plus-stubs',
            '--force' => true,
        ]);

        $this->info('Installation complete!');

        return self::SUCCESS;
    }
}
