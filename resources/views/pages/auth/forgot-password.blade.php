<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware(['guest']);
name('password.forgot');

new class extends Component {
    // forgot password
};
?>

<x-layouts.auth>
    @volt('pages.auth.forgot-password')
        <h1>Forgot Password</h1>
    @endvolt
</x-layouts.auth>

