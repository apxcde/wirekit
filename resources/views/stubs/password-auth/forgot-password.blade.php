<?php

use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

use function Laravel\Folio\{name, middleware};

middleware('guest');

name('password.request');

new class extends Component {
    #[Validate(['required', 'email', 'exists:users'])]
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate();
        Password::sendResetLink($this->only('email'));
        Session::flash('status', 'A reset link will be sent if the account exists.');
    }
}

?>

<x-layouts.base>
    <div class="flex min-h-screen flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="flex justify-center mb-8">
                <a href="/">
                    <img src="{{ asset('logo.svg') }}" alt="Logo" class="h-16 w-auto dark:invert">
                </a>
            </div>
            @volt('forgot-password')
                <div class="bg-white dark:bg-zinc-800 px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                    @if(session('status'))
                        <flux:callout class="mb-4" variant="success" icon="check-circle" heading="{{ session('status') }}" />
                    @endif

                    <div class="mb-6">
                        <flux:heading size="lg" level="1">Forgot your password?</flux:heading>
                        <flux:text class="mt-2 text-gray-600 dark:text-gray-400">
                            No problem. Just let us know your email address and we will email you a password reset link.
                        </flux:text>
                    </div>

                    <div class="space-y-4">
                        <flux:input wire:model="email" type="email" label="Email" />

                        <flux:button wire:click="sendPasswordResetLink" variant="primary" class="w-full mt-4">
                            Email Password Reset Link
                        </flux:button>
                    </div>

                    <div class="mt-6 text-center text-sm">
                        <flux:link href="{{ route('login') }}" variant="subtle">
                            ← Back to login
                        </flux:link>
                    </div>
                </div>
            @endvolt
        </div>
    </div>
</x-layouts.base>
