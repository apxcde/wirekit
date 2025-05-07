<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware(['guest']);
name('login');

new class extends Component {
    // login
};
?>

<x-layouts.auth>
    @volt('pages.auth.login')
        <h1>Login</h1>
    @endvolt
</x-layouts.auth>
