<!-- TopAppBar -->
<header class="top-header">
    <div class="header-left">
        <div class="brand" onclick="window.location.href='{{ route('home') }}'" style="cursor: pointer;">
            <div class="brand-logo font-headline-sm">K</div>
            <h1 class="brand-name font-headline-sm">Kids Avon</h1>
        </div>
    </div>
    <div class="header-right">
        <a href="{{ route('notifications') }}" class="icon-btn" aria-label="Notifications">
            <span class="material-symbols-outlined">notifications</span>
            @if ($currentRider && ($unread = $currentRider->notifications()->whereNull('read_at')->count()))
                <span class="unread-count">{{ $unread > 9 ? '9+' : $unread }}</span>
            @endif
        </a>
        <a href="{{ route('trophies') }}" class="icon-btn" aria-label="My trophies">
            <span class="material-symbols-outlined">emoji_events</span>
        </a>
        @if ($currentRider)
            <div class="user-profile" onclick="this.classList.toggle('menu-active')">
                <img alt="User Avatar" src="{{ $currentRider->avatar_or_placeholder }}" />
                <div class="user-info">
                    <p class="user-name font-label-lg">{{ $currentRider->name }}</p>
                    <p class="user-level font-label-sm">{{ $currentRider->level_title }}</p>
                </div>
                <span class="material-symbols-outlined hamburger-icon">menu</span>
                <div class="profile-dropdown soft-shadow">
                    <a href="{{ route('progress') }}" class="dropdown-item font-body-md">
                        <span class="material-symbols-outlined">trending_up</span> My Progress
                    </a>
                    <a href="{{ route('challenges.index') }}" class="dropdown-item font-body-md">
                        <span class="material-symbols-outlined">flag</span> Challenges
                    </a>
                    <a href="{{ route('settings.edit') }}" class="dropdown-item font-body-md">
                        <span class="material-symbols-outlined">settings</span> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item logout font-body-md">
                        <span class="material-symbols-outlined">logout</span> Log Out
                    </a>
                </div>
            </div>
        @endif
    </div>
</header>
