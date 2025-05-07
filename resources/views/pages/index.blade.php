<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware(['auth', 'verified']);
name('dashboard');

new class extends Component {
    // dashboard
};
?>

<x-layouts.app>
    <h1>Hello World</h1>
</x-layouts.app>
