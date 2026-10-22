<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Support\SitePage;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(string $slug): View
    {
        if (SitePage::pageNameForSlug($slug) === null) {
            abort(404);
        }

        $pageContent = SitePage::findPublishedContent($slug);

        if ($pageContent === null) {
            abort(404);
        }

        $pageContent->load('page');

        return view('Website.legal-page', [
            'pageContent' => $pageContent,
            'slug' => $slug,
        ]);
    }
}
