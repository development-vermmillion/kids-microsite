@props(['name', 'label', 'checked' => false, 'hint' => null, 'full' => false])

<div class="field {{ $full ? 'full' : '' }}">
    <input type="hidden" name="{{ $name }}" value="0" />
    <label class="check">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) {{ $attributes }} />
        {{ $label }}
    </label>
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
</div>
