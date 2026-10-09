@extends('frontend.layouts.app')

@use('App\Support\Format')

@section('title', 'Settings')
@section('body_class', 'ride-page')

@section('content')
    <div class="upload-content">
        <div class="upload-card soft-shadow">
            <div class="form-header">
                <div class="icon-circle shadow-sm">
                    <span class="material-symbols-outlined icon" style="font-variation-settings: 'FILL' 1;">settings</span>
                </div>
                <h1 class="font-headline-lg">My Profile</h1>
                <p class="font-body-lg text-variant">Change your rider name and profile photo.</p>
                <div class="settings-stats">
                    <span>{{ $rider->level_title }}</span>
                    <span>{{ Format::km($verifiedKm) }} km ridden</span>
                    <span>{{ $badgesEarned }} {{ str('badge')->plural($badgesEarned) }}</span>
                </div>
            </div>

            <form class="upload-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="input-group full-width @error('name') has-error @enderror">
                        <label class="font-label-lg" for="name">Rider Name</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">person</span>
                            <input id="name" name="name" type="text" required maxlength="100"
                                value="{{ old('name', $rider->name) }}" class="font-body-lg" />
                        </div>
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group">
                        <label class="font-label-lg">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">alternate_email</span>
                            <input type="text" value="{{ $rider->username }}" disabled class="font-body-lg" />
                        </div>
                    </div>

                    <div class="input-group @error('mobile') has-error @enderror">
                        <label class="font-label-lg" for="mobile">Mobile Number</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">smartphone</span>
                            <input id="mobile" name="mobile" type="tel" inputmode="numeric" maxlength="14" required
                                value="{{ old('mobile', $rider->mobile) }}" class="font-body-lg" />
                        </div>
                        @error('mobile')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group full-width">
                        <label class="font-label-lg">Email</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">mail</span>
                            <input type="text" value="{{ $rider->email }}" disabled class="font-body-lg" />
                        </div>
                        <p class="form-note">You log in with this email. To change your email or username, write to {{ $supportEmail }}.</p>
                    </div>

                    <div class="input-group full-width file-upload-group @error('avatar') has-error @enderror">
                        <label class="font-label-lg" for="avatar">Profile Photo</label>
                        <div class="settings-avatar">
                            @if ($rider->avatar_url)
                                <img src="{{ $rider->avatar_url }}" alt="Current photo" />
                                <label class="font-body-md" style="display:flex;gap:8px;align-items:center;cursor:pointer">
                                    <input type="checkbox" name="remove_avatar" value="1" /> Remove photo
                                </label>
                            @else
                                <span class="initials">{{ $rider->initials }}</span>
                            @endif
                        </div>
                        <div class="file-upload-wrapper" id="avatar-wrapper">
                            <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="file-input" />
                            <div class="upload-placeholder">
                                <span class="material-symbols-outlined icon">add_a_photo</span>
                                <p class="font-body-md text-main">Click to choose a new photo</p>
                                <p class="font-label-sm text-variant">JPG or PNG, up to 2 MB</p>
                            </div>
                            <div class="file-chosen">
                                <img id="avatar-preview" alt="" />
                                <div><span id="avatar-name"></span><small>Tap to choose a different photo</small></div>
                            </div>
                        </div>
                        @error('avatar')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button class="submit-btn font-headline-sm chunky-shadow-btn" type="submit">
                    Save Profile
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('avatar').addEventListener('change', (e) => {
            const file = e.target.files[0];
            const wrapper = document.getElementById('avatar-wrapper');
            if (!file) return wrapper.classList.remove('has-file');
            document.getElementById('avatar-name').textContent = file.name;
            document.getElementById('avatar-preview').src = URL.createObjectURL(file);
            wrapper.classList.add('has-file');
        });
    </script>
@endpush
