<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

final class CleanUp
{
    use AsAction;

    public $commandSignature = 'kit:clean-up';

    public $commandDescription = 'Deletes installation files including this cleanup command.';

    public $installationDir;

    public function asCommand(Command $command)
    {
        $this->installationDir = app_path('Actions/Commands/Kit');

        $command->line("Cleaning up installation files in: $this->installationDir");

        if (!is_dir($this->installationDir)) {
            $command->warn("Directory does not exist: $this->installationDir");
            return;
        }

        $this->handle($command);
    }
    
    public function handle(Command $command)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->installationDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }

        rmdir($this->installationDir);

        $command->info('✅ Kit installation directory removed (including this command).');

        return;
    }
}
