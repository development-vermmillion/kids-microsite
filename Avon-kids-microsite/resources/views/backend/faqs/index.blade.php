@extends('backend.layouts.app')

@section('title', 'FAQs')

@section('content')
    <div class="page-head">
        <div>
            <h1>FAQs</h1>
            <p>The “FAQ / Suggested Questions” section at the bottom of the home page.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined">add</span> Add question
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num">#</th>
                        <th>Question & answer</th>
                        <th>Status</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($faqs as $faq)
                        <tr>
                            <td class="num muted">{{ $faq->sort_order }}</td>
                            <td>
                                <div class="who" style="align-items:flex-start">
                                    <span class="icon-chip {{ $faq->color }}"><span class="material-symbols-outlined">{{ $faq->icon }}</span></span>
                                    <div>
                                        <strong>{{ $faq->question }}</strong>
                                        <div class="muted" style="font-size:14px">{{ $faq->answer }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($faq->is_active)
                                    <span class="pill pill-verified">Live</span>
                                @else
                                    <span class="pill pill-off">Hidden</span>
                                @endif
                            </td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-light btn-sm">
                                        <span class="material-symbols-outlined">edit</span> Edit</a>
                                    <x-admin.delete :action="route('admin.faqs.destroy', $faq)" confirm="Delete this question?" label="" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No questions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
