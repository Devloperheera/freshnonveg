<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\CatalogApi;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', 1)
            ->withCount(['products' => fn ($q) => $q->where('status', 1)])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(fn (Category $c) => CatalogApi::category($c, true))->values()->all(),
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        if (! $category->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $category->loadCount(['products' => fn ($q) => $q->where('status', 1)]);

        return response()->json([
            'success' => true,
            'data' => CatalogApi::category($category, true),
        ]);
    }
}
