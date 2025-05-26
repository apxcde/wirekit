<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;
use function Laravel\Prompts\select;

final class SelectAppLayout
{
    use AsAction;

    public $commandSignature = 'kit:select-app-layout';

    public $commandDescription = 'Select application layout';

    public function asCommand(Command $command): void
    {
        $command->line('Select application layout');
        $command->line('');

        $this->handle($command);
    }

    public function handle(Command $command): void
    {
        $appLayout = select(
            label: 'Select authentication layout',
            options: ['header', 'sidebar'],
            default: 'sidebar'
        );

        $newContent = <<<BLADE
            <x-layouts.app.{$appLayout} :title="\$title ?? null">
                <flux:main>
                    {{ \$slot }}
                </flux:main>
            </x-layouts.app.{$appLayout}>
        BLADE;

        $appLayoutFilePath = resource_path('views/components/layouts/app.blade.php');

        if (File::exists($appLayoutFilePath)) {
            File::put($appLayoutFilePath, $newContent);
            $command->info('auth.blade.php has been updated with the selected layout: ' .$appLayout);
        } else {
            $command->error('The auth.blade.php file does not exist at the expected location.');
        }
        $command->line('');
    }
}
