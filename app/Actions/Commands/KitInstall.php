<?php

namespace App\Actions\Commands;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class KitInstall
{
    use AsAction;

    public $commandSignature = 'kit:install {name? : Project name}';

    public $commandDescription = 'Run WireKit installation process';

    public function handle()
    {
        //
    }

    public function asCommand(Command $command)
    {
        $command->info('Running WireKit installation process...');
    }
}
