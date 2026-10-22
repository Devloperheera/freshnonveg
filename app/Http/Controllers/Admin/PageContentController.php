<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageContentController extends Controller
{
    public function index(): View
    {
        $contents = PageContent::query()
            ->with('page')
            ->orderByDesc('id')
            ->get();

        return view('Admin.PageContents.index', compact('contents'));
    }

    public function create(): View
    {
        return view('Admin.PageContents.form', [
            'pageContent' => new PageContent(['status' => 1]),
            'pages' => $this->pageOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PageContent::query()->create($this->validated($request));

        return redirect()->route('admin.page-contents.index')->with('success', 'Page content saved successfully.');
    }

    public function edit(PageContent $pageContent): View
    {
        return view('Admin.PageContents.form', [
            'pageContent' => $pageContent,
            'pages' => $this->pageOptions(),
        ]);
    }

    public function update(Request $request, PageContent $pageContent): RedirectResponse
    {
        $pageContent->update($this->validated($request));

        return redirect()->route('admin.page-contents.index')->with('success', 'Page content updated successfully.');
    }

    public function destroy(PageContent $pageContent): RedirectResponse
    {
        $pageContent->delete();

        return redirect()->route('admin.page-contents.index')->with('success', 'Page content deleted.');
    }

    public function toggleStatus(PageContent $pageContent): RedirectResponse
    {
        $pageContent->update(['status' => $pageContent->isActive() ? 0 : 1]);

        return back()->with('success', 'Page content status updated.');
    }

    /**
     * @return array<int, string>
     */
    private function pageOptions(): array
    {
        return Page::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'page_id' => ['required', 'integer', 'exists:pages,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:0,1'],
        ]);

        $data['page_id'] = (int) $data['page_id'];
        $data['status'] = (int) $data['status'];
        $data['content'] = $data['content'] ?? null;

        return $data;
    }
}
