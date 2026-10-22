<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Website\ShopController;
use App\Models\Product;
use App\Support\CatalogApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 24), 1), 100);

        $query = ShopController::catalogQuery($request);

        $paginator = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => collect($paginator->items())
                ->map(fn (Product $p) => CatalogApi::productSummary($p))
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        if (! $product->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $product->load(['category', 'tags', 'weights', 'images']);

        $relatedProducts = Product::query()
            ->where('status', 1)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->with(['images', 'weights', 'tags', 'category'])
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => CatalogApi::productDetail($product),
            'related' => $relatedProducts
                ->map(fn (Product $p) => CatalogApi::productSummary($p))
                ->values()
                ->all(),
        ]);
    }
}
