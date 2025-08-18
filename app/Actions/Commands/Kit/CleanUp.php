<?php

namespace App\Actions\Commands\Kit;

use Lorisleiva\Actions\Concerns\AsCommand;
use Illuminate\Console\Command;

final class CleanUp
{
    use AsCommand;

    public $commandSignature = 'kit:clean-up {--auth-views-only}';

    public $commandDescription = 'Deletes installation files including this cleanup command.';

    public function asCommand(Command $command)
    {
        if ($command->option('auth-views-only')) {
            KitHelpers::cleanupAuthViews();
            $command->info('🧹 Cleaned up authentication views.');
            return;
        }

        KitHelpers::cleanupStubsDirectory();
        $command->info('🧹 Cleaned up stubs directory.');

        KitHelpers::cleanupInstallationFiles();
        $command->info('🧹 Cleaned up installation directory.');

        $this->removeKitCommandsFromComposerJson();
        $command->info('🧹 Kit commands removed from composer.json.');

        if (InitializeGit::hasGitRepository()) {
            $command->info('Committing changes...');
            InitializeGit::commit('cleaned up wirekit installation files.');
        }

        return;
    }

    private function removeKitCommandsFromComposerJson(): void
    {
        $composerPath = base_path('composer.json');
        $json = json_decode(file_get_contents($composerPath), true);

        if (!isset($json['scripts']['post-create-project-cmd'])) {
            return;
        }

        $json['scripts']['post-create-project-cmd'] = array_values(array_filter(
            $json['scripts']['post-create-project-cmd'],
            fn($cmd) => !in_array($cmd, [
                '@php artisan kit:install',
                '@php artisan kit:activate-flux',
                '@php artisan kit:initialize-git',
                '@php artisan kit:clean-up'
            ])
        ));

        file_put_contents($composerPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
