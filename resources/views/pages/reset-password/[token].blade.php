<?php

use Livewire\Attributes\Locked;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules;

use function Laravel\Folio\{name, middleware};

middleware('guest');

name('password.reset');

new class extends Component {
    #[Locked]
    public string $token;

    #[Locked]
    public string $email = '';

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}

?>

<x-layouts.base>
    <div class="flex min-h-screen flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="flex justify-center mb-8">
                <a href="/">
                    <img src="{{ asset('logo.svg') }}" alt="Logo" class="h-16 w-auto">
                </a>
            </div>
            @volt('reset-password')
                <div class="bg-white dark:bg-zinc-800 px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                    <div class="mb-6">
                        <flux:heading size="lg" level="1">Reset Password</flux:heading>
                        <flux:text class="mt-2 text-gray-600 dark:text-gray-400">
                            Enter your email address and choose a new password.
                        </flux:text>
                    </div>

                    <div class="space-y-4">
                        <flux:input wire:model="email" type="email" label="Email" disabled />
                        <flux:input wire:model="password" type="password" label="New Password" viewable />
                        <flux:input wire:model="password_confirmation" type="password" label="Confirm New Password" viewable />

                        <flux:button wire:click="resetPassword" variant="primary" class="w-full mt-4">
                            Reset Password
                        </flux:button>
                    </div>

                    <div class="mt-6 text-center">
                        <flux:link href="{{ route('login') }}" variant="subtle">
                            ← Back to login
                        </flux:link>
                    </div>
                </div>
            @endvolt
        </div>
    </div>
</x-layouts.base>
