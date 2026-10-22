<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Services\PincodeLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class DeliveryZoneController extends Controller
{
    public function index(): View
    {
        $zones = DeliveryZone::query()
            ->withCount('pincodes')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('Admin.DeliveryZones.index', compact('zones'));
    }

    public function create(): View
    {
        return view('Admin.DeliveryZones.form', [
            'zone' => new DeliveryZone([
                'type' => DeliveryZone::TYPE_POLYGON,
                'status' => 1,
                'sort_order' => 0,
                'center_lat' => 28.5912,
                'center_lng' => 77.3525,
                'radius_km' => 15,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $pincodes] = $this->validated($request);

        DB::transaction(function () use ($data, $pincodes) {
            $zone = DeliveryZone::query()->create($data);
            $zone->pincodes()->createMany($pincodes);
        });

        return redirect()->route('admin.delivery-zones.index')->with('success', 'Delivery zone added.');
    }

    public function edit(DeliveryZone $deliveryZone): View
    {
        $deliveryZone->load('pincodes');

        return view('Admin.DeliveryZones.form', ['zone' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        [$data, $pincodes] = $this->validated($request);

        DB::transaction(function () use ($deliveryZone, $data, $pincodes) {
            $deliveryZone->update($data);
            $deliveryZone->pincodes()->delete();
            $deliveryZone->pincodes()->createMany($pincodes);
        });

        return redirect()->route('admin.delivery-zones.index')->with('success', 'Delivery zone updated.');
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->delete();

        return redirect()->route('admin.delivery-zones.index')->with('success', 'Delivery zone deleted.');
    }

    public function toggleStatus(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update(['status' => $deliveryZone->isActive() ? 0 : 1]);

        return back()->with('success', 'Delivery zone status updated.');
    }

    public function pincodeLookup(string $pincode, PincodeLookupService $lookup): JsonResponse
    {
        if (! preg_match('/^\d{6}$/', $pincode)) {
            return response()->json(['success' => false, 'message' => 'Valid 6-digit pincode.'], 422);
        }

        try {
            return response()->json(['success' => true, ...$lookup->lookup($pincode)]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, array<string, ?string>>}
     */
    private function validated(Request $request): array
    {
        $type = (string) $request->input('type');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(DeliveryZone::TYPES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['nullable', 'boolean'],
        ];

        if ($type === DeliveryZone::TYPE_RADIUS) {
            $rules += [
                'center_lat' => ['required', 'numeric', 'between:-90,90'],
                'center_lng' => ['required', 'numeric', 'between:-180,180'],
                'radius_km' => ['required', 'numeric', 'min:0.1', 'max:200'],
                'center_address' => ['nullable', 'string', 'max:255'],
            ];
        } elseif ($type === DeliveryZone::TYPE_PINCODE) {
            $rules += [
                'pincodes' => ['required', 'array', 'min:1'],
                'pincodes.*.pincode' => ['required', 'regex:/^\d{6}$/', 'distinct'],
                'pincodes.*.area_name' => ['nullable', 'string', 'max:255'],
                'pincodes.*.city' => ['nullable', 'string', 'max:100'],
                'pincodes.*.state' => ['nullable', 'string', 'max:100'],
            ];
        } elseif ($type === DeliveryZone::TYPE_POLYGON) {
            $rules['polygon'] = ['required', 'string'];
        }

        $data = $request->validate($rules, [
            'pincodes.required' => 'Kam se kam ek pincode add karein.',
            'pincodes.*.pincode.required' => 'Har row me pincode bharein.',
            'pincodes.*.pincode.regex' => 'Pincode 6 digit ka hona chahiye.',
            'pincodes.*.pincode.distinct' => 'Ek hi pincode do baar add hai.',
            'center_lat.required' => 'Map par click karke center set karein.',
        ]);

        $zone = [
            'name' => $data['name'],
            'type' => $type,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => $request->boolean('status') ? 1 : 0,
            'center_lat' => null,
            'center_lng' => null,
            'radius_km' => null,
            'center_address' => null,
            'polygon' => null,
        ];
        $pincodes = [];

        if ($type === DeliveryZone::TYPE_RADIUS) {
            $zone['center_lat'] = round((float) $data['center_lat'], 7);
            $zone['center_lng'] = round((float) $data['center_lng'], 7);
            $zone['radius_km'] = round((float) $data['radius_km'], 2);
            $zone['center_address'] = $data['center_address'] ?? null;
        } elseif ($type === DeliveryZone::TYPE_PINCODE) {
            foreach ($data['pincodes'] as $row) {
                $pincodes[] = [
                    'pincode' => $row['pincode'],
                    'area_name' => $row['area_name'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? null,
                ];
            }
        } elseif ($type === DeliveryZone::TYPE_POLYGON) {
            $zone['polygon'] = $this->parsePolygon($data['polygon']);
        }

        return [$zone, $pincodes];
    }

    /**
     * @return array<int, array{0: float, 1: float}>
     */
    private function parsePolygon(string $json): array
    {
        $points = json_decode($json, true);
        $clean = [];

        if (is_array($points)) {
            foreach ($points as $point) {
                if (is_array($point) && isset($point[0], $point[1]) && is_numeric($point[0]) && is_numeric($point[1])
                    && abs((float) $point[0]) <= 90 && abs((float) $point[1]) <= 180) {
                    $clean[] = [round((float) $point[0], 7), round((float) $point[1], 7)];
                }
            }
        }

        if (count($clean) < 3) {
            throw ValidationException::withMessages([
                'polygon' => 'Map par kam se kam 3 points click karke area banayein.',
            ]);
        }

        return $clean;
    }
}
