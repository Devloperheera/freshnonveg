<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvantageItem;
use App\Models\HomeSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvantageItemController extends Controller
{
    public function index(): View
    {
        $advantageItems = AdvantageItem::query()->orderBy('sort_order')->orderBy('id')->get();
        $section = HomeSection::forKey(HomeSection::KEY_ADVANTAGES);

        return view('Admin.AdvantageItems.index', compact('advantageItems', 'section'));
    }

    public function create(): View
    {
        return view('Admin.AdvantageItems.form', [
            'advantageItem' => new AdvantageItem(['status' => 1, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AdvantageItem::query()->create($this->validated($request));

        return redirect()->route('admin.advantage-items.index')->with('success', 'Advantage added successfully.');
    }

    public function edit(AdvantageItem $advantageItem): View
    {
        return view('Admin.AdvantageItems.form', compact('advantageItem'));
    }

    public function update(Request $request, AdvantageItem $advantageItem): RedirectResponse
    {
        $advantageItem->update($this->validated($request));

        return redirect()->route('admin.advantage-items.index')->with('success', 'Advantage updated successfully.');
    }

    public function destroy(AdvantageItem $advantageItem): RedirectResponse
    {
        $advantageItem->delete();

        return redirect()->route('admin.advantage-items.index')->with('success', 'Advantage deleted.');
    }

    public function toggleStatus(AdvantageItem $advantageItem): RedirectResponse
    {
        $advantageItem->update(['status' => $advantageItem->isActive() ? 0 : 1]);

        return back()->with('success', 'Advantage status updated.');
    }

    public function updateSection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
        ]);

        HomeSection::query()->updateOrCreate(
            ['section_key' => HomeSection::KEY_ADVANTAGES],
            $data
        );

        return back()->with('success', 'Section heading updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'icon' => ['nullable', 'string', 'max:32'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['status'] = (int) $data['status'];
        $data['icon'] = $data['icon'] ?? null;

        return $data;
    }
}
