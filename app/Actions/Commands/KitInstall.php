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
        $command->info('Starting WireKit installation process...');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command)
    {
        $this->handleGitRepository($command);
    }

    protected function handleGitRepository(Command $command)
    {
        $command->info('Checking if git repository exists...');
        $command->line('');

        if (File::isDirectory(base_path('.git'))) {
            $command->info('Git repository already exists.');
            $command->line('');
            return;
        }

        $this->initializeGit = confirm('Initialize Git repository after installation?', true);
    }
}
