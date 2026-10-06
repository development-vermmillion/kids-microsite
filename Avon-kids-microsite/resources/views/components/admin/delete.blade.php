@props(['action', 'confirm' => 'Delete this item? This cannot be undone.', 'label' => 'Delete'])

<form method="POST" action="{{ $action }}" class="inline" onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-danger btn-sm']) }}>
        <span class="material-symbols-outlined">delete</span> {{ $label }}
    </button>
</form>
