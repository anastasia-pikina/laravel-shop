<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    @stack('style')
</head>
<body class="container-fluid" style="height: 100%; margin: 0;"> <div class="wrapper row" style="height: 100%;">
    <nav id="sidebar" class="p-3 bg-dark d-flex flex-column col-2" style="height: 100vh;">
        @include('dashboard.layouts.sidebar') </nav>
    <div id="content" class="col" style="height: 100vh; overflow-y: auto;">
        @include('dashboard.layouts.nav')
        <div class="container-fluid mt-4 mb-4">
            @if(Session::has('global'))
                <div class="alert alert-success"> {{ Session::get('global') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
