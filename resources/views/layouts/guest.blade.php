<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/jpeg" href="{{ asset('od-logo.jpg') }}">
        <link rel="apple-touch-icon" href="{{ asset('od-logo.jpg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-opta-grey antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-white via-opta-teal-light/10 to-opta-sky/20">
            <div class="inline-flex rounded-xl border border-opta-teal-light/45 bg-white/90 p-2 shadow-sm shadow-opta-teal-dark/[0.06] ring-1 ring-opta-teal-dark/[0.04]">
                <a href="/" class="block rounded-lg overflow-hidden">
                    <img src="{{ asset('od-logo.jpg') }}" alt="{{ config('app.name') }}" class="h-14 w-auto" width="120" height="56" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-5 bg-white/95 shadow-lg shadow-opta-teal-dark/5 border border-opta-teal-light/30 overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="w-full sm:max-w-md mt-4 px-1">
                    {{ $footer }}
                </div>
            @endisset
        </div>
        <x-flash-toast />
    </body>
</html>
