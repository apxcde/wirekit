<?php

namespace App\Actions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

use function Laravel\Prompts\confirm;

final class KitInstall
{
    use AsAction;

    public $commandSignature = 'kit:install {name? : Project name}';

    public $commandDescription = 'Run WireKit installation process';

    private $initializeGit = false;

    public function asCommand(Command $command)
    {
        $command->line('');
        $command->line('Starting WireKit installation process...');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command)
    {
        $this->handleGitRepository($command);

        $this->setUpEnvFile($command);

        $this->reloadEnvironment();

        $this->runMigrations($command);
    }

    private function handleGitRepository(Command $command)
    {
        $command->line('Checking if git repository exists...');
        $command->line('');

        if (File::isDirectory(base_path('.git'))) {
            $command->info('Git repository already exists.');
            $command->line('');
            return;
        }

        $this->initializeGit = confirm('Initialize Git repository after installation?', true);
    }

    private function setUpEnvFile(Command $command)
    {
        if (! File::exists('.env') && File::exists('.env.example')) {
            $command->line('Creating .env file...');
            File::copy('.env.example', '.env');
        }

        $envContent = File::get('.env');
        if (! preg_match('/^APP_ENV=local/m', $envContent)) {
            $this->updateEnv('APP_ENV', 'local');
        }
    }

    private function updateEnv(string $key, string $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}=\"{$value}\"",
                file_get_contents($path)
            ));
        }
    }

    private function runMigrations(Command $command)
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

    private function reloadEnvironment()
    {
        $app = app();
        $app->bootstrapWith([
            \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        ]);
    }
}
