<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\CatalogApi;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::query()
            ->where('status', 1)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $banners->map(fn (Banner $b) => CatalogApi::banner($b))->values()->all(),
        ]);
    }
}
