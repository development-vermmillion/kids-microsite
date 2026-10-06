@props(['name', 'label', 'options' => [], 'value' => null, 'hint' => null, 'full' => false, 'placeholder' => null])

@php($id = 'f_'.$name)
@php($selected = (string) old($name, $value))
<div class="field {{ $full ? 'full' : '' }} @error($name) has-error @enderror">
    <label for="{{ $id }}">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'input']) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
