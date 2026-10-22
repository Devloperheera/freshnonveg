<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()->orderByDesc('id')->get();

        return view('Admin.Pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('Admin.Pages.form', [
            'page' => new Page(['status' => 1]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:0,1'],
        ]);

        Page::query()->create([
            'name' => $data['name'],
            'status' => (int) $data['status'],
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page added successfully.');
    }

    public function edit(Page $page): View
    {
        return view('Admin.Pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:0,1'],
        ]);

        $page->update([
            'name' => $data['name'],
            'status' => (int) $data['status'],
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    public function toggleStatus(Page $page): RedirectResponse
    {
        $page->update(['status' => $page->isActive() ? 0 : 1]);

        return back()->with('success', 'Page status updated.');
    }
}
