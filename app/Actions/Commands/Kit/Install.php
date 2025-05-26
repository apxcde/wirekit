<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

use function Laravel\Prompts\{confirm, text};

final class Install
{
    use AsAction;

    public $commandSignature = 'kit:install';

    public $commandDescription = 'Run WireKit installation process';

    private bool $initializeGit = false;

    public function asCommand(Command $command): void
    {
        $command->line('Starting WireKit installation process...');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command): void
    {
        $this->runMigrations();
    }

    private function runMigrations(): void
    {
        if (confirm('Do you want to run database migrations?', true)) {
            $command->line('Running database migrations...');
            $command->line('');

            if (! file_exists(database_path('database.sqlite'))) {
                file_put_contents(database_path('database.sqlite'), '');

                $command->info('Created database.sqlite file.');
                $command->line('');
            }

            $command->call('migrate', [
                '--force' => true,
                '--ansi' => true,
            ]);
        }
    }
}
