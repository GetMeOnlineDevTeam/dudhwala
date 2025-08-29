<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJ+Stc6RR2/fz1tXpIWZBszkxEocG4ZPBEdW8="
        crossorigin="anonymous">
    </script>

    <!-- 2) Then your app.js (or bootstrap.js) which uses $ -->
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- 3) Then Alpine (since you’re using x-data / x-show) -->
    <script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex items-center justify-center" style="width: 100%;">
        {{ $slot }}
    </div>
</body>

</html>