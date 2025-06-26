<?php

use function Laravel\Folio\{name, middleware};

middleware('auth:web');

name('account');

?>

<x-layouts.app title="Account">
    <flux:heading size="xl" level="1">Account</flux:heading>
    <flux:text class="mt-2 mb-6 text-base">Manage your account settings and preferences</flux:text>
    
    <flux:separator variant="subtle" />
</x-layouts.app>
