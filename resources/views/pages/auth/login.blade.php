<?php

use function Laravel\Folio\{middleware, name};

use Livewire\Volt\Component;
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

middleware(['guest']);
name('login');

new class extends Component {
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
};
?>

<x-layouts.auth>
    @volt('pages.auth.login')
        <div>
            <form class="space-y-6" wire:submit="login">
                <flux:input wire:model="form.email" label="Email address" type="email" />
                <flux:input wire:model="form.password" label="Password" type="password" />

                <div class="flex items-center justify-between">
                    <flux:field variant="inline">
                        <flux:checkbox wire:model="form.remember" />
                        <flux:label>Remember me</flux:label>
                        <flux:error name="terms" />
                    </flux:field>
                    <div class="text-sm/6">
                        <flux:link variant="ghost">Forgot password</flux:link>
                    </div>
                </div>
                <flux:button variant="primary" class="w-full" type="submit">Sign in</flux:button>
            </form>
        </div>
    @endvolt
</x-layouts.auth>
