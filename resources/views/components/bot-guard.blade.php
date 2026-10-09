{{-- Invisible bot traps (see App\Support\BotGuard). People never see or fill these. --}}
<div class="kv-trap" aria-hidden="true">
    <label>Leave this empty <input type="text" name="{{ \App\Support\BotGuard::TRAP_FIELD }}" value="" tabindex="-1" autocomplete="off" /></label>
</div>
<input type="hidden" name="{{ \App\Support\BotGuard::TIME_FIELD }}" value="{{ \App\Support\BotGuard::stamp() }}" />
