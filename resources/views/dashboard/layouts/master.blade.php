<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('style')
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="wrapper flex min-h-screen">
    <nav id="sidebar" class="w-64 bg-gray-900 p-4 flex flex-col" style="height: 100vh; position: sticky; top: 0;">
        @include('dashboard.layouts.sidebar')
    </nav>
    <div id="content" class="flex-1 overflow-y-auto" style="height: 100vh;">
        @include('dashboard.layouts.nav')
        <div class="p-6">
            @if(Session::has('global'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4"> {{ Session::get('global') }} </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
