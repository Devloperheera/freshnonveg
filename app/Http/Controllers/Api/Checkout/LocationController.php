<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Website\AddressSuggestController;
use App\Http\Controllers\Website\PincodeController as WebsitePincodeController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function pincode(string $pincode, WebsitePincodeController $pincodeLookup): JsonResponse
    {
        return $pincodeLookup->lookup($pincode);
    }

    public function addressSuggest(Request $request, AddressSuggestController $suggest): JsonResponse
    {
        return $suggest($request);
    }
}
