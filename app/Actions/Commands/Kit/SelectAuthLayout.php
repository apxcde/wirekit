<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;
use function Laravel\Prompts\select;

final class SelectAuthLayout
{
    use AsAction;

    public $commandSignature = 'kit:select-auth-layout';

    public $commandDescription = 'Select authentication layout';

    public function asCommand(Command $command): void
    {
        $command->line('Select authentication layout');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command): void
    {
        $authLayout = select(
            label: 'Select authentication layout',
            options: ['card', 'simple', 'split'],
            default: 'simple'
        );

        $newContent = <<<BLADE
            <x-layouts.auth.{$authLayout} :title="\$title ?? null">
                {{ \$slot }}
            </x-layouts.auth.{$authLayout}>
        BLADE;

        $authLayoutFilePath = resource_path('views/components/layouts/auth.blade.php');

        if (File::exists($authLayoutFilePath)) {
            File::put($authLayoutFilePath, $newContent);
            $command->info('auth.blade.php has been updated with the selected layout: ' .$authLayout);
        } else {
            $command->error('The auth.blade.php file does not exist at the expected location.');
        }
        $command->line('');
    }
}
