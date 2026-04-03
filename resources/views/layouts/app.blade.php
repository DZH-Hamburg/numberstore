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
    <body class="font-sans antialiased bg-opta-teal-light/15 text-opta-grey">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-opta-teal-light/40 bg-white">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8 text-left">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-12">
                {{ $slot }}
            </main>
        </div>
        <x-flash-toast />
    </body>
</html>
