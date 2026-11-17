<?php

namespace App\Support;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

final class WhatsAppOrderMessage
{
    /**
     * @param  Collection<int, \App\Models\CartItem>|null  $cartItems
     */
    public static function waMeUrl(?User $user = null, ?Product $productContext = null, ?Collection $cartItems = null): string
    {
        $number = preg_replace('/\D+/', '', (string) config('services.whatsapp.number', '919717156362'));
        if ($number === '') {
            return '';
        }

        $message = self::build($user, $productContext, $cartItems);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>|null  $cartItems
     */
    public static function build(?User $user, ?Product $productContext = null, ?Collection $cartItems = null): string
    {
        $lines = [];
        $lines[] = '*Fresh Non Veg – Order Request*';
        $lines[] = '';
        $lines[] = 'Namaste! Main order place karna chahta/chahti hoon.';
        $lines[] = '';

        $items = $cartItems ?? collect();

        if ($items->isNotEmpty()) {
            $lines[] = '*Cart items:*';
            $index = 1;
            $subtotal = 0.0;
            foreach ($items as $item) {
                $lineTotal = $item->lineTotal();
                $subtotal += $lineTotal;
                $part = $index.'. '.$item->product_name;
                if ($item->weight_label) {
                    $part .= ' ('.$item->weight_label.')';
                }
                $part .= ' × '.$item->quantity;
                $part .= ' — ₹'.number_format($lineTotal, 2);
                if ($item->preference_note) {
                    $part .= ' | Preference: '.$item->preference_note;
                }
                $lines[] = $part;
                $index++;
            }
            $lines[] = '*Approx. cart total:* ₹'.number_format($subtotal, 2);
            $lines[] = '';
        } elseif ($productContext instanceof Product) {
            $lines[] = '*Product (is page se):*';
            $lines[] = $productContext->name;
            $weights = $productContext->relationLoaded('weights')
                ? $productContext->weights
                : $productContext->weights()->get();
            if ($weights->isNotEmpty()) {
                $options = $weights->pluck('weight_label')->filter()->implode(', ');
                $lines[] = 'Weight/Qty: (choose — '.$options.')';
            } else {
                $lines[] = 'Weight/Qty: ';
            }
            $lines[] = 'Cut / Preference: ';
            $lines[] = '';
        } else {
            $lines[] = '*Order items:*';
            $lines[] = '1. Product: ';
            $lines[] = '   Weight/Qty: ';
            $lines[] = '   Cut / Preference: ';
            $lines[] = '';
        }

        $name = trim((string) ($user?->name ?? ''));
        $phone = trim((string) ($user?->phone ?? ''));
        $address = trim((string) ($user?->address ?? ''));

        $lines[] = '*Delivery details:*';
        $lines[] = 'Name: '.($name !== '' ? $name : '—');
        $lines[] = 'Mobile: '.($phone !== '' ? $phone : '—');
        $lines[] = 'Pincode: ';
        $lines[] = 'City / Area: ';
        $lines[] = 'Full address: '.($address !== '' ? $address : '—');
        $lines[] = '';
        $lines[] = '*Delivery schedule:*';
        $lines[] = 'Type: Scheduled / Instant';
        $lines[] = 'Date & time slot: ';
        $lines[] = '';
        $lines[] = '*Payment:* COD / Online (UPI)';
        $lines[] = '';
        $lines[] = 'Order notes: ';
        $lines[] = '';
        $lines[] = 'Page: '.url()->current();

        return implode("\n", $lines);
    }
}
