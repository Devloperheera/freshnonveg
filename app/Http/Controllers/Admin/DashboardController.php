<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SpeedCache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('Admin.dashboard', SpeedCache::dashboard());
    }
}
