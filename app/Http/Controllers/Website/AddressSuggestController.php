<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AddressSuggestController extends Controller
{
    /** India bounding box for Photon (lon min, lat min, lon max, lat max) */
    private const INDIA_BBOX = '68.1,6.5,97.4,35.7';

    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['success' => true, 'suggestions' => []]);
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'User-Agent' => 'FreshNonVeg-Checkout/1.0 ('.config('app.url').')',
        ])
            ->timeout(5)
            ->get('https://photon.komoot.io/api/', [
                'q' => $q,
                'limit' => 8,
                'lang' => 'en',
                'bbox' => self::INDIA_BBOX,
            ]);

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Address suggestions unavailable.',
                'suggestions' => [],
            ], 502);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();
        /** @var array<int, array<string, mixed>> $features */
        $features = $payload['features'] ?? [];

        $suggestions = collect($features)->map(function (array $feature) {
            /** @var array<string, mixed> $props */
            $props = $feature['properties'] ?? [];

            $label = $this->formatLabel($props);
            $addressLine = $this->formatAddressLine($props);

            return [
                'label' => $label,
                'address' => $addressLine,
                'pincode' => (string) ($props['postcode'] ?? ''),
                'city' => (string) ($props['city'] ?? $props['county'] ?? $props['district'] ?? ''),
                'state' => (string) ($props['state'] ?? ''),
            ];
        })->filter(fn (array $row) => $row['label'] !== '')->values()->all();

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function formatLabel(array $props): string
    {
        $parts = array_filter([
            $props['name'] ?? null,
            $props['street'] ?? null,
            $props['housenumber'] ?? null,
            $props['city'] ?? null,
            $props['state'] ?? null,
            $props['postcode'] ?? null,
        ]);

        return implode(', ', array_unique(array_map('strval', $parts)));
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function formatAddressLine(array $props): string
    {
        $streetParts = array_filter([
            $props['housenumber'] ?? null,
            $props['street'] ?? null,
            $props['name'] ?? null,
        ]);

        $line = implode(' ', array_map('strval', $streetParts));

        if ($line === '') {
            return $this->formatLabel($props);
        }

        return $line;
    }
}
