<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PageApi;
use App\Support\SitePage;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [];

        foreach (SitePage::SLUGS as $slug => $pageName) {
            $content = SitePage::findPublishedContent($slug);
            if ($content !== null) {
                $data[] = PageApi::summary($slug, $content);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        if (SitePage::pageNameForSlug($slug) === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid page slug.',
            ], 404);
        }

        $content = SitePage::findPublishedContent($slug);

        if ($content === null) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found or not published.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => PageApi::detail($slug, $content),
        ]);
    }
}
