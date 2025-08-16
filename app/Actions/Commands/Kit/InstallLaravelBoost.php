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
            default: true,
        );

        $confirmed ? $this->handle($command) : 
            $command->comment('Skipping Laravel Boost installation...');
    }

    public function handle(Command $command): void
    {
        $this->installLaravelBoost($command);
        $confirmed = confirm(
            label: 'Activate Laravel Boost?',
            default: true,
        );

        $confirmed ? $this->activateLaravelBoost($command) : 
            $command->comment('Skipping Laravel Boost activation...');
    }

    public function installLaravelBoost(Command $command): void
    {
        $command->line('Installing Laravel Boost...');
        $command->line('');

        exec('composer require laravel/boost --dev');

        $command->info('Laravel Boost package installed as a dev dependency.');
        $command->line('');
    }

    public function activateLaravelBoost(Command $command): void
    {
        $command->line('Activating Laravel Boost...');
        $command->line('');

        exec('php artisan boost:install');

        $command->info('Laravel Boost activated successfully.');
        $command->line('');
    }
}
