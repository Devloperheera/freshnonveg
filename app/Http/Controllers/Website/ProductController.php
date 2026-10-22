<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View|RedirectResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $product->load(['category', 'tags', 'weights', 'images']);

        $relatedProducts = Product::query()
            ->where('status', 1)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->with(['images', 'weights'])
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get();

        $visibleReviews = $product->reviews()->visible();
        $ratingSummary = [
            'count' => (clone $visibleReviews)->count(),
            'average' => round((float) (clone $visibleReviews)->avg('rating'), 1),
            'breakdown' => (clone $visibleReviews)
                ->selectRaw('rating, COUNT(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating'),
        ];
        $reviews = (clone $visibleReviews)->with('user:id,name,profile_image')->latest('id')->limit(10)->get();

        return view('Website.product-show', compact('product', 'relatedProducts', 'ratingSummary', 'reviews'));
    }
}
