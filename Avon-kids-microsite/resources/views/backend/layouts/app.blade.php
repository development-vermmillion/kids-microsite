<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title', 'Dashboard') · Kids Avon Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="{{ asset('backend/css/admin.css') }}" rel="stylesheet" />
</head>

<body>
    @php
        $nav = [
            ['Overview', null],
            ['Dashboard', 'admin.dashboard', 'dashboard', 'admin.dashboard'],
            ['Riders & rides', null],
            ['Ride review', 'admin.rides.index', 'fact_check', 'admin.rides.*', $pendingRidesCount],
            ['Riders', 'admin.riders.index', 'group', 'admin.riders.*'],
            ['Site content', null],
            ['Challenges', 'admin.challenges.index', 'flag', 'admin.challenges.*'],
            ['Badges & trophies', 'admin.badges.index', 'military_tech', 'admin.badges.*'],
            ['Notifications', 'admin.notifications.index', 'notifications', 'admin.notifications.*'],
            ['FAQs', 'admin.faqs.index', 'help', 'admin.faqs.*'],
            ['Site settings', 'admin.settings.edit', 'tune', 'admin.settings.*'],
            ['Admin', null],
            ['Admin users', 'admin.admins.index', 'admin_panel_settings', 'admin.admins.*'],
        ];
    @endphp

    <div class="admin-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="logo">K</div>
                <div>
                    <strong>Kids Avon</strong>
                    <small>Admin panel</small>
                </div>
            </div>

            <nav>
                @foreach ($nav as $item)
                    @if ($item[1] === null)
                        <div class="nav-label">{{ $item[0] }}</div>
                    @else
                        <a href="{{ route($item[1]) }}" class="nav-link {{ request()->routeIs($item[3]) ? 'active' : '' }}">
                            <span class="material-symbols-outlined">{{ $item[2] }}</span>
                            {{ $item[0] }}
                            @if (! empty($item[4]))
                                <span class="count">{{ $item[4] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach

                <div class="nav-label">Website</div>
                <a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener">
                    <span class="material-symbols-outlined">open_in_new</span> View site
                </a>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar">
                <button type="button" class="btn btn-light btn-sm menu-toggle"
                    onclick="document.body.classList.toggle('nav-open')" aria-label="Menu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="topbar-title">
                    <strong>@yield('title', 'Dashboard')</strong>
                    <span>{{ now()->format('l, j F Y') }}</span>
                </div>
                <div class="topbar-right">
                    <span class="topbar-user">
                        <span class="initial">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="name">{{ auth()->user()->name }}</span>
                    </span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">
                            <span class="material-symbols-outlined">logout</span> Log out
                        </button>
                    </form>
                </div>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert-success">
                        <span class="material-symbols-outlined">check_circle</span> {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error">
                        <span class="material-symbols-outlined">error</span> {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error">
                        <span class="material-symbols-outlined">error</span>
                        Please fix the highlighted fields below.
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Close the mobile menu when tapping outside it.
        document.addEventListener('click', (e) => {
            if (document.body.classList.contains('nav-open')
                && !e.target.closest('#sidebar') && !e.target.closest('.menu-toggle')) {
                document.body.classList.remove('nav-open');
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
