<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DeliverySlot extends Model
{
    public const WINDOW_START = '09:00';

    public const WINDOW_END = '21:00';

    public const MAX_SLOTS = 12;

    protected $fillable = [
        'slot_key',
        'label',
        'start_time',
        'end_time',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function startInput(): string
    {
        return self::normalizeTime((string) $this->start_time);
    }

    public function endInput(): string
    {
        return self::normalizeTime((string) $this->end_time);
    }

    public static function normalizeTime(string $time): string
    {
        if (preg_match('/^(\d{2}):(\d{2})/', trim($time), $match)) {
            return $match[1].':'.$match[2];
        }

        return trim($time);
    }

    public static function makeKey(string $start, string $end): string
    {
        return self::normalizeTime($start).'-'.self::normalizeTime($end);
    }

    public static function makeLabel(string $start, string $end): string
    {
        $from = Carbon::createFromFormat('H:i', self::normalizeTime($start));
        $to = Carbon::createFromFormat('H:i', self::normalizeTime($end));

        return $from->format('h:i A').' – '.$to->format('h:i A');
    }
}
