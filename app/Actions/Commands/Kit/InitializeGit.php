<?php

namespace App\Actions\Commands\Kit;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;

final class InitializeGit
{
    use AsAction;

    public $commandSignature = 'kit:initialize-git';

    public $commandDescription = 'Initialize Git repository';

    private bool $initializeGit = false;
    
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

        self::commit('Initial commit');

        $command->info('Git repository initialized.');
        $command->line('');
    }

    public function asCommand(Command $command)
    {
        $command->line('Checking if git repository exists...');
        $command->line('');

        $this->handle($command);

        if (self::hasGitRepository()) {
            $command->info('Git repository already exists.');
            $command->line('');
            return;
        }

        $this->initializeGit = confirm('Initialize Git repository after installation?', true);

        $this->handle($command);
    }

    public static function hasGitRepository()
    {
        return File::isDirectory(base_path('.git'));
    }

    public static function commit(string $message)
    {
        exec('git add .');
        exec('git commit -m "WIREKIT: ' . $message . '"');
    }
}
