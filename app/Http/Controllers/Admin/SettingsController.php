<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CheckoutDelivery;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('Admin.Settings.index', [
            'instantDeliveryFee' => CheckoutDelivery::instantFee(),
            'whatsappNumber' => (string) config('services.whatsapp.number', ''),
            'appUrl' => (string) config('app.url'),
        ]);
    }
}