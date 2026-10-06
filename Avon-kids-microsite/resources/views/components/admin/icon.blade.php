@props(['name' => 'icon', 'label' => 'Icon', 'value' => null, 'color' => 'primary'])

@php($id = 'f_'.$name)
<div class="field @error($name) has-error @enderror">
    <label for="{{ $id }}">{{ $label }}</label>
    <div class="input-icon-preview">
        <span class="icon-chip preview {{ $color }}"><span class="material-symbols-outlined" data-icon-preview="{{ $id }}">{{ old($name, $value) }}</span></span>
        <input id="{{ $id }}" name="{{ $name }}" type="text" value="{{ old($name, $value) }}" class="input" required
            oninput="document.querySelector('[data-icon-preview={{ $id }}]').textContent = this.value" />
    </div>
    <div class="hint">A Material Symbols icon name, e.g. <code>directions_bike</code>, <code>stars</code>, <code>map</code>.
        <a href="https://fonts.google.com/icons" target="_blank" rel="noopener">Browse icons</a></div>
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
