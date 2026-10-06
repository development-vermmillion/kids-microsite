@props(['name', 'label', 'current' => null, 'hint' => 'JPG, PNG or WEBP, up to 4 MB.', 'removable' => true, 'full' => false])

@php($id = 'f_'.$name)
<div class="field {{ $full ? 'full' : '' }} @error($name) has-error @enderror">
    <label for="{{ $id }}">{{ $label }}</label>
    @if ($current)
        <div style="display:flex; gap:12px; align-items:center; margin-bottom:8px;">
            <img src="{{ $current }}" alt="" class="thumb" style="width:64px;height:64px;" />
            @if ($removable)
                <label class="check" style="font-weight:600;font-size:14px;">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" /> Remove current image
                </label>
            @endif
        </div>
    @endif
    <input id="{{ $id }}" name="{{ $name }}" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="input" />
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
