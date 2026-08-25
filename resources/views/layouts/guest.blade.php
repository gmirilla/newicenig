<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="icon" href="{{ asset('images/icen-mark.jpg') }}" type="image/jpeg">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <img src="{{ asset('images/icen-mark.jpg') }}" alt="ICEN" class="h-14 w-14 rounded-full object-cover">
            </a>

            <div class="mt-8 w-full sm:max-w-md">
                <x-card>
                    {{ $slot }}
                </x-card>
            </div>
        </div>
    </body>
</html>
