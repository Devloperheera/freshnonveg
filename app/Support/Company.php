<?php

namespace App\Support;

class Company
{
    public static function brand(): string
    {
        return (string) config('company.brand');
    }

    public static function legalName(): string
    {
        return (string) config('company.legal_name');
    }

    public static function gstin(): string
    {
        return (string) config('company.gstin');
    }

    public static function pan(): string
    {
        return (string) config('company.pan');
    }

    public static function addressLine(): string
    {
        $a = config('company.address', []);

        return collect([
            $a['floor'] ?? null,
            isset($a['building']) ? 'No. '.$a['building'] : null,
            $a['street'] ?? null,
            $a['locality'] ?? null,
            $a['city'] ?? null,
            ($a['state'] ?? null).' — '.($a['pincode'] ?? null),
        ])->filter()->implode(', ');
    }

    /**
     * @return array<string, mixed>
     */
    public static function billHeader(): array
    {
        return [
            'brand' => self::brand(),
            'legal_name' => self::legalName(),
            'trade_name' => (string) config('company.trade_name'),
            'gstin' => self::gstin(),
            'pan' => self::pan(),
            'constitution' => (string) config('company.constitution'),
            'registration_type' => (string) config('company.registration_type'),
            'state' => (string) config('company.state'),
            'state_code' => (string) config('company.state_code'),
            'pincode' => (string) config('company.pincode'),
            'address' => self::addressLine(),
            'address_parts' => config('company.address', []),
            'tagline' => 'FRESH MEAT · NEVER FROZEN',
        ];
    }
}
