<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-100">
<head>
    @include('partials.head')
</head>
    <body class="h-full">
        <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                    {{ $slot }}
                </div>
            </div>
        </div>
    @fluxScripts
    </body>
</html>
