<?php

namespace App\Actions\Commands\Kit;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Console\Command;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

final class CleanUp
{
    use AsAction;

    public $commandSignature = 'kit:clean-up';

    public $commandDescription = 'Deletes installation files including this cleanup command.';

    public $installationDir;

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
        $command->line('');

        $this->removeKitCommandsFromComposerJson($command);

        if (InitializeGit::hasGitRepository()) {
            $command->info('Committing changes...');
            InitializeGit::commit('Remove kit commands from composer.json');
        }

        return;
    }

    private function removeKitCommandsFromComposerJson(Command $command): void
    {
        $composerPath = base_path('composer.json');
        $json = json_decode(file_get_contents($composerPath), true);

        if (!isset($json['scripts']['post-create-project-cmd'])) {
            $command->warn('No post-create-project-cmd section found.');
            return;
        }

        $json['scripts']['post-create-project-cmd'] = array_values(array_filter(
            $json['scripts']['post-create-project-cmd'],
            fn($cmd) => !in_array($cmd, [
                '@php artisan kit:install',
                '@php artisan kit:initialize-git',
                '@php artisan kit:clean-up'
            ])
        ));

        file_put_contents($composerPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        $command->info('🧹 kit commands removed from composer.json.');
    }

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
}
