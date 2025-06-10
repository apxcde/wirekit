<?php

use function Laravel\Folio\{name, middleware};

middleware('auth:web');

name('dashboard');

?>

<x-layouts.app title="Dashboard">
    <flux:heading size="xl" level="1">Good afternoon, Olivia</flux:heading>
    <flux:text class="mt-2 mb-6 text-base">Here's what's new today</flux:text>
    
    <flux:separator variant="subtle" />
</x-layouts.app>
