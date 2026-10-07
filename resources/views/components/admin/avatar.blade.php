@props(['rider', 'size' => ''])

@if ($rider->avatar_url)
    <img src="{{ $rider->avatar_url }}" alt="" class="avatar {{ $size }}" />
@else
    <span class="avatar {{ $size }}">{{ $rider->initials }}</span>
@endif
