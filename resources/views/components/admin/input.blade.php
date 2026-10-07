@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'full' => false])

@php($id = 'f_'.str_replace(['[', ']', '.'], '_', $name))
<div class="field {{ $full ? 'full' : '' }} @error($name) has-error @enderror">
    <label for="{{ $id }}">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'input']) }} />
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
