<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Support\SpeedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->hasAny(['category', 'tag', 'product', 'q'])) {
            return redirect()->route('shop.index', $request->only(['category', 'tag', 'product', 'q']));
        }

        return view('Website.home', SpeedCache::home($request));
    }
}
