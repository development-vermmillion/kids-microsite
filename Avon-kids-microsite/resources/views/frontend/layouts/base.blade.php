<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Kids Avon - @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="{{ asset('frontend/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('frontend/css/additions.css') }}" rel="stylesheet" />
    @stack('styles')
</head>

<body @hasSection('body_class') class="@yield('body_class')" @endif>
    @yield('body')

    @stack('scripts')
</body>

</html>
