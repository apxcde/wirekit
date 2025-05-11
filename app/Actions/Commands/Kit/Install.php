<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

final class Install
{
    use AsAction;

    public $commandSignature = 'kit:install';

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
        $this->setUpEnvFile($command);

        $this->setProjectName($command);

        $this->reloadEnvironment();

        $this->runMigrations($command);

        return;
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

    private function setUpEnvFile(Command $command)
    {
        if (! File::exists('.env') && File::exists('.env.example')) {
            $command->line('Creating the .env file...');
            File::copy('.env.example', '.env');
        }

        $envContent = File::get('.env');
        if (! preg_match('/^APP_ENV=local/m', $envContent)) {
            $this->updateEnv('APP_ENV', 'local');
        }
    }

    private function reloadEnvironment()
    {
        $app = app();
        $app->bootstrapWith([
            \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        ]);
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

    private function setProjectName(Command $command)
    {
        if (env('APP_NAME') !== 'WireKit') {
            $command->line('Project name already set. Skipping.');
            return;
        }

        $defaultName = basename(getcwd());
        $name = text(
            label: 'What is the name of your project?',
            placeholder: $defaultName,
            default: $defaultName,
            required: true
        );

        $this->updateEnv('APP_NAME', $name);

        $defaultUrl = "http://{$name}.test";
        $url = text(
            label: 'What is the URL of your project?',
            placeholder: $defaultUrl,
            default: $defaultUrl,
            required: true
        );

        $this->updateEnv('APP_URL', $url);
    }
}
