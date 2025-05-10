<?php

namespace App\Actions\Commands;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\{confirm, text, select};

final class ActivateFlux
{
    use AsAction;

    public $commandSignature = 'kit:activate-flux';

    public $commandDescription = 'Run Flux activation process';

    public function asCommand(Command $command)
    {
        $confirmed = confirm(
            label: 'Do you want to activate Flux Pro?',
            default: true,
        );
        $confirmed ? $this->handle($command) : $command->comment('Skipping Flux activation...');
    }

    public function handle(Command $command)
    {
        $installationType = select(
            label: 'How do you want to activate Flux?',
            options: [
                'File' => 'Copy my auth.json file',
                'License' => 'I have a username and license key',
            ],
            default: 'File',
        );

        $installationType === 'File' ? $this->handleFileInstallation($command) : $this->handleLicenseInstallation($command);
    }

    protected function handleFileInstallation(Command $command)
    {
        $command->info('Activating Flux...');
        $command->line('');

        $sourceAuthJson = $this->getFluxAuthJsonPath($command);

        $command->line('Copying auth.json to application...');
        File::copy($sourceAuthJson, base_path('auth.json'));

        $command->info('File copied successfully.');
        $command->line('');

        $this->addFluxProRepositoryToComposerJson($command);
    }

    private function getFluxAuthJsonPath(Command $command)
    {
        $defaultSourceAuthJson = $_SERVER['HOME'].'/Herd/.flux/auth.json';
        if (File::exists($defaultSourceAuthJson)) {
            return $defaultSourceAuthJson;
        }

        while (! File::exists($defaultSourceAuthJson)) {
            $command->error('File does not exist.');
    
            $defaultSourceAuthJson = text(
                label: 'Where is your Flux auth.json file located?',
                placeholder: $defaultSourceAuthJson,
                default: $defaultSourceAuthJson,
                required: true
            );
        }

        return $defaultSourceAuthJson;
    }

    protected function handleLicenseInstallation(Command $command)
    {
        $command->info('Activating Flux...');
        $command->line('');

        $command->line('Running flux:activate command...');
        $command->line('');

        $command->call('flux:activate');
    }

    protected function addFluxProRepositoryToComposerJson(Command $command)
    {
        $command->line('Adding Flux Pro repository to composer.json...');
        $command->line('');

        $composerJson = json_decode(File::get(base_path('composer.json')), true);

        if (! isset($composerJson['repositories']['flux-pro'])) {
            $composerJson['repositories']['flux-pro'] = [
                'type' => 'composer',
                'url' => 'https://composer.fluxui.dev',
            ];
        }

        if (! isset($composerJson['require']['livewire/flux-pro'])) {
            $composerJson['require']['livewire/flux-pro'] = '^2.0';
        }

        File::put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $command->line('Running composer update to install Flux Pro...');
        exec('composer update livewire/flux-pro --no-interaction');

        $command->info('Flux Pro activated successfully.');
        $command->line('');
    }
}
