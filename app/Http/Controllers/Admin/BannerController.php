<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Support\UploadRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()->withCount('products')->orderByDesc('id')->get();

        return view('Admin.Banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('Admin.Banners.form', [
            'banner' => new Banner(['status' => 1]),
            'productOptions' => $this->productOptions(),
            'selectedProductIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => UploadRules::image(),
            'status' => ['required', 'in:0,1'],
            'product_ids' => ['nullable', 'array', 'max:100'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $path = $request->file('image')->store('banners', 'public');

        $banner = Banner::query()->create([
            'name' => $data['name'],
            'image' => $path,
            'status' => (int) $data['status'],
        ]);
        $this->syncProducts($banner, $data['product_ids'] ?? []);

        return redirect()->route('admin.banners.index')->with('success', 'Banner added successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('Admin.Banners.form', [
            'banner' => $banner,
            'productOptions' => $this->productOptions(),
            'selectedProductIds' => $banner->products()->pluck('products.id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    /**
     * @return Collection<int, Product>
     */
    private function productOptions(): Collection
    {
        return Product::query()->with('category:id,name')->orderBy('name')->get(['id', 'name', 'status', 'category_id']);
    }

    /**
     * Banner ke products save karo — jis order me admin ne chune wahi order website par.
     *
     * @param  array<int, int|string>  $productIds
     */
    private function syncProducts(Banner $banner, array $productIds): void
    {
        $sync = [];
        foreach (array_values(array_unique(array_map('intval', $productIds))) as $i => $id) {
            $sync[$id] = ['sort_order' => $i];
        }
        $banner->products()->sync($sync);
        $banner->touch(); // home page cache refresh
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => UploadRules::image(false),
            'status' => ['required', 'in:0,1'],
            'product_ids' => ['nullable', 'array', 'max:100'],
            'product_ids.*' => ['integer', 'exists:products,id'],
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
        $this->syncProducts($banner, $data['product_ids'] ?? []);

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
