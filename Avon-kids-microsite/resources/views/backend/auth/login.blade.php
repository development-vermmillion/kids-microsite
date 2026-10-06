<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Admin login · Kids Avon</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="{{ asset('backend/css/admin.css') }}" rel="stylesheet" />
</head>

<body>
    <div class="login-page">
        <div class="card login-card">
            <div class="brand">
                <img src="{{ asset('frontend/images/avon-new-logo.jpeg') }}" alt="Avon" />
                <h1>Kids Avon Admin</h1>
                <p class="muted">Sign in to manage the microsite</p>
            </div>

            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                <div class="field @error('email') has-error @enderror">
                    <label for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required
                        autofocus autocomplete="username" />
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field @error('password') has-error @enderror">
                    <label for="password">Password</label>
                    <input class="input" id="password" type="password" name="password" required
                        autocomplete="current-password" />
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label class="check"><input type="checkbox" name="remember" value="1" /> Keep me signed in</label>
                </div>
                <button type="submit" class="btn btn-primary">
                    Sign in <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </form>
        </div>
    </div>
</body>

</html>
