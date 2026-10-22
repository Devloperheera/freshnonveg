<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteStat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteStatController extends Controller
{
    public function index(): View
    {
        $siteStats = SiteStat::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('Admin.SiteStats.index', compact('siteStats'));
    }

    public function create(): View
    {
        return view('Admin.SiteStats.form', [
            'siteStat' => new SiteStat(['status' => 1, 'sort_order' => 0, 'value' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SiteStat::query()->create($this->validated($request));

        return redirect()->route('admin.site-stats.index')->with('success', 'Stat added successfully.');
    }

    public function edit(SiteStat $siteStat): View
    {
        return view('Admin.SiteStats.form', compact('siteStat'));
    }

    public function update(Request $request, SiteStat $siteStat): RedirectResponse
    {
        $siteStat->update($this->validated($request));

        return redirect()->route('admin.site-stats.index')->with('success', 'Stat updated successfully.');
    }

    public function destroy(SiteStat $siteStat): RedirectResponse
    {
        $siteStat->delete();

        return redirect()->route('admin.site-stats.index')->with('success', 'Stat deleted.');
    }

    public function toggleStatus(SiteStat $siteStat): RedirectResponse
    {
        $siteStat->update(['status' => $siteStat->isActive() ? 0 : 1]);

        return back()->with('success', 'Stat status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'value' => ['required', 'integer', 'min:0', 'max:99999999'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ]);

        $data['value'] = (int) $data['value'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['status'] = (int) $data['status'];

        return $data;
    }
}
