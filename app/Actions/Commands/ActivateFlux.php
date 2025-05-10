<?php

namespace App\Actions\Commands;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

use function Laravel\Prompts\confirm;

final class ActivateFlux
{
    use AsAction;

    public $commandSignature = 'kit:activate-flux';

    public $commandDescription = 'Run Flux activation process';

    public function asCommand(Command $command)
    {
        $confirmed = confirm('Do you want to activate Flux Pro?', default: true);

        if (! $confirmed) {
            return;
        }

        $command->info('Activating Flux...');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command)
    {
        return;
    }
}
