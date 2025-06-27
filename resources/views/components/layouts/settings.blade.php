<x-layouts.app title="{{ $title }}">
    <div class="flex flex-col space-y-2">
        <flux:heading size="xl" level="1">Settings</flux:heading>
        <flux:text class="mt-2 mb-6 text-base">Manage your profile and account settings </flux:text>
        <flux:separator />
    </div>
    <div class="flex flex-col md:flex-row mt-8">
        <flux:navlist class="w-64">
            <flux:navlist.item href="{{ route('settings.profile') }}">Profile</flux:navlist.item>
            <flux:navlist.item href="{{ route('settings.appearance') }}">Appearance</flux:navlist.item>
        </flux:navlist>
        <flux:separator class="md:hidden block my-8" />
        <div class="md:pl-8 pl-0 md:w-2xl">
            {{ $slot }}
        </div>
    </div>
</x-layouts.app>
