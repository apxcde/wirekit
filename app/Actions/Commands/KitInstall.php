<?php

namespace App\Actions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

final class KitInstall
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
        $this->handleGitRepository($command);

        $this->setUpEnvFile($command);

        $this->reloadEnvironment();

        $this->runMigrations($command);

        $this->setProjectName($command);

        $this->handleFluxActivation($command);

        $this->initializeGitRepository($command);

        return 0;
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

    private function handleFluxActivation(Command $command)
    {
        $defaultSourceAuthJson = $_SERVER['HOME'].'/.flux/auth.json';
        $sourceAuthJson = text(
            label: 'Where is your Flux auth.json file located?',
            placeholder: $defaultSourceAuthJson,
            default: $defaultSourceAuthJson,
            required: true
        );

        if (File::exists($sourceAuthJson)) {
            $command->line('Found auth.json. Copying to application...');
            $command->line('');

            File::copy($sourceAuthJson, base_path('auth.json'));
            $command->info('auth.json copied successfully.');
            $command->line('');

            $command->line('Adding Flux Pro repository to composer.json...');
            $command->line('');

            if (! isset($composerJson['repositories']['flux-pro'])) {
                $composerJson['repositories']['flux-pro'] = [
                    'type' => 'composer',
                    'url' => 'https://composer.fluxui.dev',
                ];
            }

            if (! isset($composerJson['require']['livewire/flux-pro'])) {
                $composerJson['require']['livewire/flux-pro'] = '^2.0';
            }

            file_put_contents(
                base_path('composer.json'),
                json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            $command->line('Running composer update to install Flux Pro...');
            exec('composer update livewire/flux-pro --no-interaction');

            $command->info('Flux Pro activated successfully.');
            $command->line('');

            return;
        }

        $hasFluxPro = confirm('Do you have a Flux Pro account?', false);

        if ($hasFluxPro) {
            $command->line('Running flux:activate command...');
            $command->line('');

            $command->call('flux:activate');
        } else {
            $command->comment('This starter kit uses some Flux Pro components, however, feel free to remove them if needed.');
            $command->line('');
        }
    }

    private function initializeGitRepository(Command $command)
    {
        if ($this->initializeGit) {
            $command->line('Initializing Git repository...');
            $command->line('');

            exec('git init');

            if (! File::exists(base_path('.gitignore'))) {
                File::put(base_path('.gitignore'), implode("\n", [
                    '/.phpunit.cache',
                    '/vendor',
                    'composer.phar',
                    'composer.lock',
                    '.DS_Store',
                    'Thumbs.db',
                    '/phpunit.xml',
                    '/.idea',
                    '/.fleet',
                    '/.vscode',
                    '.phpunit.result.cache',
                ]));
                $command->info('Created .gitignore file.');
                $command->line('');
            }

            exec('git add .');
            exec('git commit -m "Initial commit"');

            $command->info('Git repository initialized with initial commit.');
            $command->line('');
        }
    }
}
