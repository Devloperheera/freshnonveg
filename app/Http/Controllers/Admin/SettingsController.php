<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\CheckoutDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('Admin.Settings.index', [
            'instantDeliveryFee' => CheckoutDelivery::instantFee(),
            'deliveryCharge' => CheckoutDelivery::deliveryCharge(),
            'freeDeliveryMinOrder' => CheckoutDelivery::freeDeliveryMinOrder(),
            'whatsappNumber' => (string) config('services.whatsapp.number', ''),
            'appUrl' => (string) config('app.url'),
        ]);
    }

    public function updateDelivery(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'delivery_charge' => ['required', 'numeric', 'min:0', 'max:10000'],
            'free_delivery_min_order' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'instant_delivery_fee' => ['required', 'numeric', 'min:0', 'max:10000'],
        ], [], [
            'delivery_charge' => 'delivery charge',
            'free_delivery_min_order' => 'free delivery minimum order',
            'instant_delivery_fee' => 'instant delivery fee',
        ]);

        AppSetting::putMany([
            'delivery_charge' => round((float) $data['delivery_charge'], 2),
            'free_delivery_min_order' => round((float) $data['free_delivery_min_order'], 2),
            'instant_delivery_fee' => round((float) $data['instant_delivery_fee'], 2),
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Delivery charges update ho gaye.');
    }
}
