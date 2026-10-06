@extends('frontend.layouts.base')

@section('body')
    <main class="app-container">
        @include('frontend.partials.header')

        @yield('content')
    </main>

    @include('frontend.partials.footer', ['footerVariant' => $footerVariant ?? 'default'])
@endsection
