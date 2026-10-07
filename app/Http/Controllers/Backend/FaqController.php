<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('backend.faqs.index', ['faqs' => Faq::orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('backend.faqs.form', ['faq' => new Faq([
            'icon' => 'help',
            'color' => 'primary',
            'is_active' => true,
            'sort_order' => (Faq::max('sort_order') ?? 0) + 1,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'Question added.');
    }

    public function edit(Faq $faq): View
    {
        return view('backend.faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'Question updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'Question deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:200'],
            'answer' => ['required', 'string', 'max:1000'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'color' => ['required', Rule::in(['primary', 'secondary', 'tertiary', 'neutral'])],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ], [
            'icon.regex' => 'Use the icon name in lowercase with underscores, e.g. help.',
        ]);
    }
}
