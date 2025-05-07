<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware(['guest']);
name('register');

new class extends Component {
    // register
};
?>

<x-layouts.auth>
    @volt('pages.auth.register')
        <h1>Register</h1>
    @endvolt
</x-layouts.auth>
