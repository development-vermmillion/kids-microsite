@extends('backend.layouts.app')

@section('title', $faq->exists ? 'Edit question' : 'Add question')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ route('admin.faqs.index') }}">← FAQs</a></p>
            <h1>{{ $faq->exists ? 'Edit question' : 'Add a question' }}</h1>
        </div>
    </div>

    <form method="POST" class="card" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
        @csrf
        @if ($faq->exists)
            @method('PUT')
        @endif

        <div class="card-pad form-grid">
            <x-admin.input name="question" label="Question" :value="$faq->question" required full />
            <x-admin.textarea name="answer" label="Answer" :value="$faq->answer" required />
            <x-admin.icon :value="$faq->icon" :color="$faq->color" />
            <x-admin.color :value="$faq->color" :neutral="true" hint="Colour of the question heading." />
            <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$faq->sort_order" required
                hint="Lower numbers show first." />
            <x-admin.checkbox name="is_active" label="Live on the website" :checked="$faq->is_active" />
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $faq->exists ? 'Save changes' : 'Add question' }}</button>
        </div>
    </form>
@endsection
