<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([
                'categories' => [],
                'subcategories' => [],
                'products' => [],
            ]);
        }

        $term = '%'.$q.'%';

        $categories = Category::query()
            ->where('status', 1)
            ->where('name', 'like', $term)
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(fn (Category $cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => 'category',
                'url' => route('shop.index', ['category' => $cat->id]),
                'image' => $cat->imageUrl(),
            ]);

        $subcategories = Tag::query()
            ->where('status', 1)
            ->where('name', 'like', $term)
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'type' => 'tag',
                'url' => route('shop.index', ['tag' => $tag->id]),
            ]);

        $products = Product::query()
            ->where('status', 1)
            ->with(['category', 'images', 'weights'])
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('meta_title', 'like', $term)
                    ->orWhere('meta_keywords', 'like', $term)
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', $term));
            })
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function (Product $product) {
                $image = $product->images->first();
                $price = $product->weights->first()?->price;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'type' => 'product',
                    'category' => $product->category?->name,
                    'url' => route('products.show', $product),
                    'image' => $image ? $image->url() : null,
                    'price' => $price !== null ? (float) $price : null,
                ];
            });

        return response()->json([
            'categories' => $categories,
            'subcategories' => $subcategories,
            'products' => $products,
        ]);
    }
}
