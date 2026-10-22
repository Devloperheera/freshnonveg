<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('Admin.Faqs.index', compact('faqs'));
    }

    public function create(): View
    {
        return view('Admin.Faqs.form', [
            'faq' => new Faq(['status' => 1, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::query()->create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added successfully.');
    }

    public function edit(Faq $faq): View
    {
        return view('Admin.Faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }

    public function toggleStatus(Faq $faq): RedirectResponse
    {
        $faq->update(['status' => $faq->isActive() ? 0 : 1]);

        return back()->with('success', 'FAQ status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['status'] = (int) $data['status'];

        return $data;
    }
}
