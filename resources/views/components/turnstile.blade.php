@props(['action' => 'form'])
{{-- Cloudflare Turnstile robot check. Renders nothing when no site key is set. --}}
@if (\App\Support\Turnstile::enabled())
    <div {{ $attributes->merge(['class' => 'turnstile-box']) }}>
        <div class="cf-turnstile" data-sitekey="{{ \App\Support\Turnstile::siteKey() }}" data-action="{{ $action }}"
            data-theme="light" data-size="flexible"></div>
        @error('cf-turnstile-response')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce
@endif
