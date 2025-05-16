<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    @include('partials.head')
</head>
    <body class="h-full dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                <div class="bg-white dark:bg-white/10 border border-zinc-200 dark:border-white/10 px-6 py-12 sm:rounded-xl sm:px-12">
                    {{ $slot }}
                </div>
            </div>
        </div>
    @fluxScripts
    </body>
</html>
