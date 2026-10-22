<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tag;
use App\Support\UploadRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [25, 50, 100, 200, 500];

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
            'category_id' => (string) $request->input('category_id', ''),
            'featured' => (string) $request->input('featured', ''),
            'tag_id' => (string) $request->input('tag_id', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];

        $query = Product::query()
            ->with(['category', 'banner', 'tags', 'weights', 'images']);

        $this->applyFilters($query, $filters);

        $products = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 1)->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];

        return view('Admin.Products.index', [
            'products' => $products,
            'stats' => $stats,
            'filters' => $filters,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->where('status', 1)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'banner', 'tags', 'weights', 'images']);

        return view('Admin.Products.show', compact('product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);

        $product = Product::query()->create(collect($data)->except(['tag_ids', 'weights', 'image_sort', 'new_images'])->all());
        $this->syncWeights($product, $data['weights']);
        $this->syncTags($product, $data['tag_ids'] ?? []);
        $this->syncImages($product, $request);
        $this->syncVideo($product, $request);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedProduct($request);

        $product->update(collect($data)->except(['tag_ids', 'weights', 'image_sort', 'new_images'])->all());
        $this->syncWeights($product, $data['weights']);
        $this->syncTags($product, $data['tag_ids'] ?? []);
        $this->syncImages($product, $request);
        $this->syncVideo($product, $request);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update(['status' => $product->isActive() ? 0 : 1]);

        return back()->with('success', 'Product status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProduct(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'banner_id' => ['nullable', 'exists:banners,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:0,1'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['nullable', 'string', 'max:255'],
            'weights' => ['required', 'array', 'min:1'],
            'weights.*.weight_label' => ['required', 'string', 'max:80'],
            'weights.*.price' => ['required', 'numeric', 'min:0'],
            'image_sort' => ['nullable', 'array'],
            'image_sort.*' => ['string', 'max:20'],
            'new_images' => ['nullable', 'array'],
            'new_images.*' => UploadRules::image(false),
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm,video/x-msvideo', 'max:51200'],
            'remove_video' => ['nullable', 'boolean'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['category_id'] = $data['category_id'] ?: null;
        $data['banner_id'] = $data['banner_id'] ?: null;

        return $data;
    }

    private function syncVideo(Product $product, Request $request): void
    {
        if ($request->boolean('remove_video') && $product->video_path) {
            if (! str_starts_with($product->video_path, 'http')) {
                Storage::disk('public')->delete($product->video_path);
            }
            $product->update(['video_path' => null]);
        }

        if ($request->hasFile('video')) {
            if ($product->video_path && ! str_starts_with($product->video_path, 'http')) {
                Storage::disk('public')->delete($product->video_path);
            }
            $path = $request->file('video')->store('products/videos', 'public');
            $product->update(['video_path' => $path]);
        }
    }

    private function syncImages(Product $product, Request $request): void
    {
        /** @var list<string> $sortKeys */
        $sortKeys = $request->input('image_sort', []);

        /** @var array<int, \Illuminate\Http\UploadedFile> $newFiles */
        $newFiles = $request->file('new_images', []) ?? [];

        if ($sortKeys === [] && $product->images()->exists() && $newFiles === []) {
            return;
        }

        $existingById = $product->images()->get()->keyBy('id');
        $orderedRecords = [];
        $sortOrder = 0;

        foreach ($sortKeys as $key) {
            if (preg_match('/^e-(\d+)$/', (string) $key, $m)) {
                $id = (int) $m[1];
                if ($existingById->has($id)) {
                    $orderedRecords[] = ['model' => $existingById->get($id), 'sort' => $sortOrder++];
                }
            } elseif (preg_match('/^n-(\d+)$/', (string) $key, $m)) {
                $idx = (int) $m[1];
                if (isset($newFiles[$idx])) {
                    $path = $newFiles[$idx]->store('products/images', 'public');
                    $orderedRecords[] = [
                        'model' => $product->images()->create(['path' => $path, 'sort_order' => 0]),
                        'sort' => $sortOrder++,
                    ];
                }
            }
        }

        $keptIds = collect($orderedRecords)
            ->map(fn ($row) => $row['model']->id)
            ->all();

        $product->images()->whereNotIn('id', $keptIds)->get()->each(function (ProductImage $image): void {
            $image->deleteFile();
            $image->delete();
        });

        foreach ($orderedRecords as $row) {
            $row['model']->update(['sort_order' => $row['sort']]);
        }

        if ($sortKeys === [] && $newFiles !== []) {
            foreach ($newFiles as $index => $file) {
                $path = $file->store('products/images', 'public');
                $product->images()->create([
                    'path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * @param  list<string|null>  $tagValues
     */
    private function syncTags(Product $product, array $tagValues): void
    {
        $ids = [];

        foreach ($tagValues as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            if (ctype_digit($value)) {
                $ids[] = (int) $value;

                continue;
            }

            $tag = Tag::query()->firstOrCreate(
                ['name' => $value],
                ['status' => 1]
            );
            $ids[] = $tag->id;
        }

        $product->tags()->sync(array_values(array_unique($ids)));
    }

    /**
     * @param  list<array{weight_label: string, price: numeric-string|float|int}>  $weights
     */
    private function syncWeights(Product $product, array $weights): void
    {
        $product->weights()->delete();

        foreach ($weights as $index => $row) {
            $product->weights()->create([
                'weight_label' => $row['weight_label'],
                'price' => $row['price'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('meta_title', 'like', $term)
                    ->orWhere('meta_keywords', 'like', $term);
            });
        }

        if ($filters['status'] === 'active') {
            $query->where('status', 1);
        } elseif ($filters['status'] === 'inactive') {
            $query->where('status', '!=', 1);
        }

        if ($filters['category_id'] !== '') {
            $query->where('category_id', $filters['category_id']);
        }

        if ($filters['featured'] === 'yes') {
            $query->where('is_featured', true);
        } elseif ($filters['featured'] === 'no') {
            $query->where('is_featured', false);
        }

        if ($filters['tag_id'] !== '') {
            $query->whereHas('tags', fn (Builder $q) => $q->where('tags.id', $filters['tag_id']));
        }

        match ($filters['sort']) {
            'oldest' => $query->orderBy('id'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'categories' => Category::query()->where('status', 1)->orderBy('name')->pluck('name', 'id'),
            'banners' => Banner::query()->where('status', 1)->orderBy('name')->pluck('name', 'id'),
            'tags' => Tag::query()->where('status', 1)->orderBy('name')->get(),
        ];
    }

    public function create(): View
    {
        return view('Admin.Products.form', array_merge([
            'product' => new Product(['status' => 1, 'is_featured' => false]),
            'weightRows' => [['weight_label' => '', 'price' => '']],
        ], $this->formOptions()));
    }

    public function edit(Product $product): View
    {
        $product->load(['weights', 'tags', 'images']);

        $weightRows = $product->weights->map(fn ($w) => [
            'weight_label' => $w->weight_label,
            'price' => $w->price,
        ])->all();

        if ($weightRows === []) {
            $weightRows = [['weight_label' => '', 'price' => '']];
        }

        return view('Admin.Products.form', array_merge([
            'product' => $product,
            'weightRows' => $weightRows,
        ], $this->formOptions()));
    }
}
