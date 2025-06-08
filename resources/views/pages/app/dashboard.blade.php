<?php

use function Laravel\Folio\{name, middleware};

middleware('auth:web');

name('dashboard');

?>

<x-layouts.app>
    <div class="container mx-auto">
        <h1>Dashboard</h1>
    </div>
</x-layouts.app>
