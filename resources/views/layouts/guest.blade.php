<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BHouse') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .guest-shell {
                background:
                    radial-gradient(circle at 15% 12%, rgba(255, 255, 255, .95), transparent 16rem),
                    linear-gradient(135deg, #edf3e9, #e2eddd);
            }
            .guest-card {
                border: 1px solid rgba(191, 205, 194, .75);
                border-radius: 24px;
                background: rgba(255, 255, 255, .96);
                padding: 28px;
                box-shadow: 0 24px 70px rgba(52, 83, 67, .12);
            }
            .guest-card input,
            .guest-card select {
                border-radius: 10px;
                border-color: #cbd8cd;
            }
            .guest-card input:focus,
            .guest-card select:focus {
                border-color: #73917c;
                --tw-ring-color: rgba(115, 145, 124, .18);
            }
            .guest-card button,
            .guest-card [type="submit"] {
                border-radius: 10px;
                background: #173f35;
            }
            @media (max-width: 639px) {
                .guest-card { border-radius: 18px; padding: 22px 18px; }
            }
        </style>
    </head>
    <body class="font-sans text-[#18332d] antialiased">
        <div class="guest-shell min-h-screen px-4 py-8 sm:flex sm:items-center sm:justify-center sm:px-6">
            <div class="guest-card mx-auto w-full max-w-md">
                <a href="/" class="mb-7 flex items-center justify-center" aria-label="Boarding House home">
                    <x-application-logo class="h-14 w-14" />
                </a>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
