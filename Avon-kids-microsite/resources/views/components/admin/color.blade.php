@props(['name' => 'color', 'label' => 'Colour', 'value' => 'primary', 'neutral' => false, 'hint' => null])

@php
    $colors = ['primary' => 'Red (primary)', 'secondary' => 'Green (secondary)', 'tertiary' => 'Blue (tertiary)'];
    if ($neutral) {
        $colors['neutral'] = 'Grey (neutral)';
    }
@endphp
<x-admin.select :name="$name" :label="$label" :options="$colors" :value="$value"
    :hint="$hint ?? 'Matches the brand colours used on the website.'" />
