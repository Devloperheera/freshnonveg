<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\DeliveryZoneChecker;
use App\Services\PincodeLookupService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class PincodeController extends Controller
{
    public function __construct(
        private PincodeLookupService $pincodeLookup,
        private DeliveryZoneChecker $zones,
    ) {}

    public function lookup(string $pincode): JsonResponse
    {
        if (! preg_match('/^\d{6}$/', $pincode)) {
            return response()->json([
                'success' => false,
                'message' => 'Valid 6-digit pincode enter karein.',
            ], 422);
        }

        $zone = $this->zones->checkPincode($pincode);
        $delivery = [
            'deliverable' => $zone['deliverable'],
            'zone' => $zone['zone']?->name,
            'message' => $zone['message'],
            'message_hi' => $zone['message_hi'] ?? null,
            'message_en' => $zone['message_en'] ?? null,
        ];

        try {
            $data = $this->pincodeLookup->lookup($pincode);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'delivery' => $delivery,
            ], 502);
        }

        return response()->json([
            'success' => true,
            ...$data,
            'delivery' => $delivery,
        ]);
    }
}
