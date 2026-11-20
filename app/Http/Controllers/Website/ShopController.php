<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->filled('product')) {
            $product = Product::query()
                ->where('status', 1)
                ->find($request->integer('product'));

            if ($product) {
                return redirect()->route('products.show', $product, 301);
            }
        }

        $query = $this->catalogQuery($request);

        $products = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        $activeCategory = $request->filled('category')
            ? Category::query()->where('status', 1)->find($request->integer('category'))
            : null;

        $activeTag = $request->filled('tag')
            ? Tag::query()->where('status', 1)->find($request->integer('tag'))
            : null;

        $activeBanner = $request->filled('banner')
            ? Banner::query()->where('status', 1)->find($request->integer('banner'))
            : null;

        return view('Website.shop', [
            'products' => $products,
            'showViewAll' => false,
            'activeCategory' => $activeCategory,
            'activeTag' => $activeTag,
            'activeBanner' => $activeBanner,
        ]);
    }

    /**
     * @return Builder<Product>
     */
    public static function catalogQuery(Request $request): Builder
    {
        $query = Product::query()
            ->where('status', 1)
            ->with(['images', 'weights', 'tags', 'category']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn (Builder $t) => $t->where('tags.id', $request->integer('tag')));
        }

        if ($request->filled('product')) {
            $query->where('id', $request->integer('product'));
        }

        // Hero banner click → sirf us banner ke products, admin wale order me
        if ($request->filled('banner')) {
            $bannerId = $request->integer('banner');
            $query->whereHas('banners', fn (Builder $b) => $b->where('banners.id', $bannerId)->where('banners.status', 1))
                ->orderByRaw(
                    '(select bp.sort_order from banner_product bp where bp.product_id = products.id and bp.banner_id = ? limit 1)',
                    [$bannerId]
                );
        }

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('meta_keywords', 'like', $term)
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $term));
            });
        }

        return $query;
    }
}
