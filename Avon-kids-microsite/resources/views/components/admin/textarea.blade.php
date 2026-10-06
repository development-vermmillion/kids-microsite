@props(['name', 'label', 'value' => null, 'hint' => null, 'full' => true, 'rows' => 3])

@php($id = 'f_'.$name)
<div class="field {{ $full ? 'full' : '' }} @error($name) has-error @enderror">
    <label for="{{ $id }}">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => 'input']) }}>{{ old($name, $value) }}</textarea>
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
