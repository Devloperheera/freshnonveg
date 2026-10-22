<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class PincodeLookupService
{
    /**
     * @return array{pincode: string, state: string, city: string}
     */
    public function lookup(string $pincode): array
    {
        return Cache::remember('pincode:'.$pincode, now()->addDays(30), function () use ($pincode) {
            $lastError = 'Pincode service unavailable. State/City manually bhar sakte hain.';

            foreach ($this->endpoints($pincode) as $url) {
                try {
                    $parsed = $this->fetchPostalPincodeIn($url, $pincode);
                    if ($parsed !== null) {
                        return $parsed;
                    }
                } catch (RuntimeException $e) {
                    $lastError = $e->getMessage();
                }
            }

            throw new RuntimeException($lastError);
        });
    }

    /**
     * @return list<string>
     */
    private function endpoints(string $pincode): array
    {
        return [
            'https://api.postalpincode.in/pincode/'.$pincode,
            'http://api.postalpincode.in/pincode/'.$pincode,
        ];
    }

    /**
     * @return array{pincode: string, state: string, city: string}|null
     */
    private function fetchPostalPincodeIn(string $url, string $pincode): ?array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (compatible; FreshNonVeg/1.0; +'.config('app.url').')',
            ])
                ->withOptions([
                    'version' => '1.1',
                    'force_ip_resolve' => 'v4',
                ])
                ->connectTimeout(8)
                ->timeout(20)
                ->retry(2, 400, throw: false)
                ->get($url);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Pincode service se connect nahi ho paya. State/City manually bhar dein.');
        } catch (Throwable $e) {
            throw new RuntimeException('Pincode lookup fail. State/City manually bhar dein.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Pincode service error (HTTP '.$response->status().').');
        }

        /** @var array<int, array<string, mixed>>|null $body */
        $body = $response->json();
        if (! is_array($body)) {
            return null;
        }

        $block = $body[0] ?? null;
        if (! is_array($block) || ($block['Status'] ?? '') !== 'Success') {
            return null;
        }

        /** @var array<int, array<string, mixed>> $offices */
        $offices = $block['PostOffice'] ?? [];
        if ($offices === []) {
            return null;
        }

        $first = $offices[0];
        $state = trim((string) ($first['State'] ?? ''));
        $city = trim((string) ($first['District'] ?? ''));

        if ($state === '' || $city === '') {
            return null;
        }

        return [
            'pincode' => $pincode,
            'state' => $state,
            'city' => $city,
            'area' => trim((string) ($first['Name'] ?? '')),
        ];
    }
}
