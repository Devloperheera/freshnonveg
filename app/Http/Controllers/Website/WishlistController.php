<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $items = auth()->user()
            ->wishlistItems()
            ->latest('id')
            ->get();

        return view('Website.wishlist', compact('items'));
    }
}
