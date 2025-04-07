<!DOCTYPE html>
{{-- <html lang="{{session('applocale')}}"> --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Hubis') }}</title>

    <!-- Fonts -->
    <link rel="icon" href="/storage/images/site_icon.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap">
    <!-- Scripts -->
    @routes
    @vite('resources/js/app.js')
    {{-- @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"]) --}}
    @inertiaHead
    
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>