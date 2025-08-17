<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

use function Laravel\Prompts\{confirm, select, text};

final class Install
{
    use AsCommand;

    public $commandSignature = 'kit:install';

    public $commandDescription = 'Run WireKit installation process';

    public function asCommand(Command $command): void
    {
        $command->line('Starting WireKit installation process...');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command): void
    {
        $this->setUpEnvFile($command);
        $this->setProjectName($command);
        $this->reloadEnvironment();
        $this->selectAuth($command);
        $this->runMigrations($command);
    }

    private function setUpEnvFile(Command $command): void
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

    private function updateEnv(string $key, string $value): void
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

    private function setProjectName(Command $command): void
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

    private function reloadEnvironment(): void
    {
        $app = app();
        $app->bootstrapWith([
            LoadEnvironmentVariables::class,
        ]);
    }

    private function selectAuth(Command $command): void
    {
        $authChoice = select(
            label: 'Select authentication scaffolding',
            options: [
                'magic-link' => 'Magic Link',
                'email-password' => 'Email & Password',
            ],
            required: true,
        );

        if ($authChoice === 'magic-link') {
            $this->installMagicLinkAuth($command);
        } else {
            $this->installPasswordAuth($command);
        }
    }

    private function installMagicLinkAuth(Command $command): void
    {
        $command->line('Installing Magic Link authentication...');

        $this->copyDirectory(
            resource_path('views/stubs/magic-auth'),
            resource_path('views/pages')
        );

        $webRoutesPath = base_path('routes/web.php');
        $webRoutesContent = File::get($webRoutesPath);
        
        if (!str_contains($webRoutesContent, "require __DIR__.'/auth.php';")) {
            $webRoutesContent .= "\nrequire __DIR__.'/auth.php';\n";
            File::put($webRoutesPath, $webRoutesContent);
        }

        $command->info('✅ Magic Link authentication installed successfully!');
        $command->line('');
    }

    private function installPasswordAuth(Command $command): void
    {
        $command->line('Installing Email & Password authentication...');
        
        $this->copyDirectory(
            resource_path('views/stubs/password-auth'),
            resource_path('views/pages')
        );

        $authRoutesPath = base_path('routes/auth.php');
        if (File::exists($authRoutesPath)) {
            File::delete($authRoutesPath);
        }

        $webRoutesPath = base_path('routes/web.php');
        $webRoutesContent = File::get($webRoutesPath);
        $webRoutesContent = str_replace("require __DIR__.'/auth.php';", '', $webRoutesContent);
        $webRoutesContent = trim($webRoutesContent) . "\n";
        File::put($webRoutesPath, $webRoutesContent);

        $command->info('✅ Email & Password authentication installed successfully!');
        $command->line('');
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $files = File::allFiles($source);
        
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $targetPath = $destination . '/' . $relativePath;
            
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            
            File::copy($file->getPathname(), $targetPath);
        }
    }

    private function runMigrations(Command $command): void
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
