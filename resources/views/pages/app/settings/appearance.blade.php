<?php

use function Laravel\Folio\{name, middleware};

middleware('auth:web');

name('settings.appearance');

?>

<x-layouts.settings title="Appearance">
    <div class="flex flex-col space-y-2">
        <flux:heading level="1">Appearance</flux:heading>
        <flux:text>Update your account's appearance settings</flux:text>
    </div>
    <div class="mt-6">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">Light</flux:radio>
            <flux:radio value="dark" icon="moon">Dark</flux:radio>
            <flux:radio value="system" icon="computer-desktop">System</flux:radio>
        </flux:radio.group>
    </div>
</x-layouts.settings>

