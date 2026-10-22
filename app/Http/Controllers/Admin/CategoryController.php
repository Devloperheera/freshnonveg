<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\UploadRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
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
            'sort' => (string) $request->input('sort', 'newest'),
            'created_from' => (string) $request->input('created_from', ''),
            'created_to' => (string) $request->input('created_to', ''),
        ];

        $query = Category::query();
        $this->applyFilters($query, $filters);

        $categories = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('status', 1)->count(),
            'inactive' => Category::where('status', '!=', 1)->count(),
        ];

        return view('Admin.Categories.index', [
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $filters,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }

    public function create(): View
    {
        return view('Admin.Categories.form', [
            'category' => new Category(['status' => 1]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => UploadRules::image(),
            'status' => ['required', 'in:0,1'],
        ]);

        $path = $request->file('image')->store('categories', 'public');

        Category::query()->create([
            'name' => $data['name'],
            'image' => $path,
            'status' => (int) $data['status'],
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category added successfully.');
    }

    public function edit(Category $category): View
    {
        return view('Admin.Categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
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
            $category->deleteImageFile();
            $updates['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($updates);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->deleteImageFile();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update(['status' => $category->isActive() ? 0 : 1]);

        return back()->with('success', 'Category status updated.');
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where('name', 'like', $term);
        }

        if ($filters['status'] === 'active') {
            $query->where('status', 1);
        } elseif ($filters['status'] === 'inactive') {
            $query->where('status', '!=', 1);
        }

        if ($filters['created_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if ($filters['created_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        match ($filters['sort']) {
            'oldest' => $query->orderBy('id'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderByDesc('id'),
        };
    }
}
