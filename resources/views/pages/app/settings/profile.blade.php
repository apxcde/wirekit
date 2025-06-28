<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

middleware('auth:web');

name('settings.profile');

new class extends Component {
    public string $name;
    public string $email;

    public $user;

    public function mount()
    {
        $this->user = Auth::user();

        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }

    public function updateProfile()
    {
        $this->validate([ 'name' => 'required|string|max:255' ]);
        $this->user->update([ 'name' => $this->name ]);
        $this->dispatch('saved');
    }    

    public function deleteAccount()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        $this->user->delete();

        return redirect('/');
    }
}

?>

<x-layouts.settings title="Profile">
    <div class="flex flex-col">
        <div class="flex flex-col space-y-2">
            <flux:heading level="2">Profile</flux:heading>
            <flux:text>Update your name and email address</flux:text>
        </div>
        @volt('profile')
            <div>
                <div class="flex flex-col gap-4 mt-4">
                    <flux:input type="text" label="Name" name="name" wire:model="name" />
                    <flux:input type="email" label="Email" name="email" wire:model="email" disabled />
                    <div class="flex items-center">
                        <flux:button variant="primary" wire:click="updateProfile">Save</flux:button>
                        <x-action-message class="ml-3" on="saved">
                            {{ __('Saved.') }}
                        </x-action-message>
                        <flux:spacer />
                    </div>
                </div>
                <div class="space-y-4 mt-10">
                    <div class="flex flex-col gap-2">
                        <flux:heading size="lg" level="2">Delete account</flux:heading>
                        <flux:text>Delete your account and all of its resources.</flux:text>
                    </div>
                    <flux:modal.trigger name="delete-account">
                        <flux:button variant="danger">Delete account</flux:button>
                    </flux:modal.trigger>

                    <flux:modal name="delete-account" class="min-w-[22rem]">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">Delete account?</flux:heading>

                                <flux:text class="mt-2">
                                    <p>You're about to delete your account.</p>
                                    <p>This action cannot be reversed.</p>
                                </flux:text>
                            </div>

                            <div class="flex gap-2">
                                <flux:spacer />

                                <flux:modal.close>
                                    <flux:button variant="ghost">Cancel</flux:button>
                                </flux:modal.close>

                                <flux:button type="submit" variant="danger" wire:click="deleteAccount">Delete account</flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </div>
            </div>
        @endvolt
    </div>
</x-layouts.settings>
