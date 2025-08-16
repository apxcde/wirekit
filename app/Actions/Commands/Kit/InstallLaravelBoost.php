<?php

namespace App\Actions\Commands\Kit;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

final class InstallLaravelBoost
{
    use AsAction;

    public $commandSignature = 'kit:install-laravel-boost';

    public $commandDescription = 'Run Laravel Boost installation process';

    public function asCommand(Command $command): void
    {
        $confirmed = confirm(
            label: 'Do you want to install Laravel Boost?',
            default: false,
        );
    
        $confirmed ? $this->handle($command) : $command->comment('Skipping Laravel Boost installation...');
    }

    public function handle(Command $command): void
    {
        $command->line('Installing Laravel Boost...');
        $command->line('');

        exec('composer require laravel/boost --dev');

        $command->info('Laravel Boost package installed as a dev dependency.');
        $command->line('');

        $command->line('Remember to run `php artisan boost:install` to install the package.');
        $command->line('');
    }
}
