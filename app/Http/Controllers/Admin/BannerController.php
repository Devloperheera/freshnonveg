<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\UploadRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()->orderByDesc('id')->get();

        return view('Admin.Banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('Admin.Banners.form', [
            'banner' => new Banner(['status' => 1]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => UploadRules::image(),
            'status' => ['required', 'in:0,1'],
        ]);

        $path = $request->file('image')->store('banners', 'public');

        Banner::query()->create([
            'name' => $data['name'],
            'image' => $path,
            'status' => (int) $data['status'],
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner added successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('Admin.Banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => UploadRules::image(false),
            'status' => ['required', 'in:0,1'],
        ]);

        $updates = [
            'name' => $data['name'],
            'status' => (int) $data['status'],
        ];

        if ($request->hasFile('image')) {
            $banner->deleteImageFile();
            $updates['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($updates);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->deleteImageFile();
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted.');
    }

    public function toggleStatus(Banner $banner): RedirectResponse
    {
        $banner->update(['status' => $banner->isActive() ? 0 : 1]);

        return back()->with('success', 'Banner status updated.');
    }
}
