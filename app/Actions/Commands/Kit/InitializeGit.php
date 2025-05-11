<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;

final class InitializeGit
{
    use AsAction;

    public $commandSignature = 'kit:initialize-git';

    public $commandDescription = 'Initialize Git repository';

    private $initializeGit = false;

    public function asCommand(Command $command)
    {
        $command->line('Checking if git repository exists...');
        $command->line('');

        if (File::isDirectory(base_path('.git'))) {
            $command->info('Git repository already exists.');
            $command->line('');
            return;
        }

        $this->initializeGit = confirm('Initialize Git repository after installation?', true);

        $this->handle($command);
    }

    public function handle(Command $command)
    {
        if (!$this->initializeGit) {
            $command->info('Skipping Git repository initialization...');
            $command->line('');
            return;
        }

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
