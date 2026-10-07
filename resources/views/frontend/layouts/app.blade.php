@extends('frontend.layouts.base')

@section('body')
    <main class="app-container">
        @include('frontend.partials.header')

        @if (session('success') || session('error'))
            <div class="site-flash" role="status">
                @if (session('success'))
                    <div class="flash flash-success">
                        <span class="material-symbols-outlined">celebration</span> {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="flash flash-error">
                        <span class="material-symbols-outlined">error</span> {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    @include('frontend.partials.footer', ['footerVariant' => $footerVariant ?? 'default'])
@endsection
