<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

/**
 * Sirf Noida (Gautam Buddha Nagar) aur Ghaziabad — baaki jagah delivery band.
 */
class NoidaGhaziabadDeliveryZonesSeeder extends Seeder
{
    /** @return list<string> */
    private static function noidaPincodes(): array
    {
        $core = [];
        for ($i = 301; $i <= 318; $i++) {
            $core[] = sprintf('201%d', $i);
        }

        return array_values(array_unique(array_merge($core, [
            '203135', '203141', '203155', '203201', '203202', '203203', '203207', '203209',
        ])));
    }

    /** @return list<string> */
    private static function ghaziabadPincodes(): array
    {
        $core = [];
        for ($i = 1; $i <= 17; $i++) {
            $core[] = sprintf('201%03d', $i);
        }

        return array_values(array_unique(array_merge($core, [
            '201020', '201102', '201103', '201201', '201204',
            '245201', '245205', '245206', '245207', '245208', '245304',
        ])));
    }

    public function run(): void
    {
        DeliveryZone::query()
            ->whereIn('name', ['Noida & Greater Noida', 'Ghaziabad'])
            ->delete();

        $definitions = [
            [
                'name' => 'Noida & Greater Noida — Pincodes',
                'type' => DeliveryZone::TYPE_PINCODE,
                'description' => 'Gautam Buddha Nagar ke saare mukhya pincode',
                'sort_order' => 1,
                'status' => 1,
                'pincodes' => self::noidaPincodes(),
            ],
            [
                'name' => 'Ghaziabad — Pincodes',
                'type' => DeliveryZone::TYPE_PINCODE,
                'description' => 'Ghaziabad district ke mukhya pincode',
                'sort_order' => 2,
                'status' => 1,
                'pincodes' => self::ghaziabadPincodes(),
            ],
            [
                'name' => 'Noida & Greater Noida — Map Area',
                'type' => DeliveryZone::TYPE_POLYGON,
                'description' => 'Geocode backup — poora GB Nagar approx boundary',
                'sort_order' => 3,
                'status' => 1,
                'polygon' => [
                    [28.325, 77.318],
                    [28.325, 77.480],
                    [28.325, 77.620],
                    [28.325, 77.705],
                    [28.420, 77.715],
                    [28.520, 77.700],
                    [28.605, 77.640],
                    [28.620, 77.500],
                    [28.605, 77.380],
                    [28.540, 77.325],
                    [28.460, 77.312],
                    [28.380, 77.312],
                ],
                'pincodes' => [],
            ],
            [
                'name' => 'Ghaziabad — Map Area',
                'type' => DeliveryZone::TYPE_POLYGON,
                'description' => 'Geocode backup — poora Ghaziabad district approx boundary',
                'sort_order' => 4,
                'status' => 1,
                'polygon' => [
                    [28.495, 77.335],
                    [28.495, 77.520],
                    [28.495, 77.720],
                    [28.495, 77.920],
                    [28.495, 78.135],
                    [28.610, 78.155],
                    [28.730, 78.145],
                    [28.865, 78.040],
                    [28.875, 77.820],
                    [28.865, 77.580],
                    [28.780, 77.400],
                    [28.650, 77.338],
                    [28.550, 77.332],
                ],
                'pincodes' => [],
            ],
        ];

        foreach ($definitions as $def) {
            $pincodes = $def['pincodes'];
            unset($def['pincodes']);

            $zone = DeliveryZone::query()->updateOrCreate(
                ['name' => $def['name']],
                $def
            );

            if ($zone->type === DeliveryZone::TYPE_PINCODE) {
                $zone->pincodes()->delete();
                $zone->pincodes()->createMany(
                    array_map(fn (string $pin) => ['pincode' => $pin], $pincodes)
                );
            }
        }

        $this->command?->info('Delivery: sirf Noida/Greater Noida + Ghaziabad. Pincode + map zones active.');
    }
}
