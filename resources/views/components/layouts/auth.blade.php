<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body class="flex items-center justify-center max-w-md min-h-screen m-auto bg-white dark:bg-zinc-800">
        <div class="w-full">
            {{ $slot }}
        </div>
        @fluxScripts
    </body>
</html>
