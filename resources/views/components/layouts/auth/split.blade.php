<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
<div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
        <div class="absolute inset-0 bg-neutral-900"></div>
        <a href="#" class="relative z-20 flex items-center text-md font-medium uppercase" wire:navigate>
            {{ config('app.name', 'WireKit') }} {{-- add a logo. --}}
        </a>
    </div>
    <div class="w-full lg:p-8">
        <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
            {{ $slot }}
        </div>
    </div>
</div>
@fluxScripts
</body>
</html>
