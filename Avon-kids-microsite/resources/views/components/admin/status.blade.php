@props(['status', 'label' => null])

@php
    $icons = ['pending' => 'hourglass_empty', 'verified' => 'check_circle', 'rejected' => 'cancel'];
    $labels = ['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'];
@endphp
<span class="pill pill-{{ $status }}">
    <span class="material-symbols-outlined">{{ $icons[$status] ?? 'help' }}</span>
    {{ $label ?? ($labels[$status] ?? ucfirst($status)) }}
</span>
