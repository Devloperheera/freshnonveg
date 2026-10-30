<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'delivery_address',
        'pincode',
        'state',
        'city',
        'post_office',
        'delivery_schedule_type',
        'delivery_date',
        'delivery_time',
        'delivery_fee',
        'payment_method',
        'customer_notes',
        'coupon_id',
        'coupon_code',
        'discount_amount',
        'wallet_amount',
        'cashfree_payment_id',
        'order_number',
        'total',
        'status',
        'bill_emailed_at',
        'confirmation_sms_sent_at',
        'cancelled_at',
        'cancel_reason',
        'admin_seen_at',
    ];

    public const PAID_STATUSES = ['paid', 'confirmed'];

    public const INSTANT_CANCEL_MINUTES = 10;

    public const SCHEDULED_CANCEL_HOURS_BEFORE = 2;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'wallet_amount' => 'decimal:2',
            'delivery_date' => 'date',
            'bill_emailed_at' => 'datetime',
            'confirmation_sms_sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'admin_seen_at' => 'datetime',
        ];
    }

    public function isPaid(): bool
    {
        return in_array($this->status, self::PAID_STATUSES, true);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function deliveryStartsAt(): ?Carbon
    {
        if ($this->delivery_schedule_type !== 'scheduled' || ! $this->delivery_date) {
            return null;
        }

        $start = DeliverySlot::normalizeTime(explode('-', (string) $this->delivery_time)[0] ?? '');
        if (! preg_match('/^\d{2}:\d{2}$/', $start)) {
            $start = DeliverySlot::WINDOW_START;
        }

        return Carbon::parse($this->delivery_date->format('Y-m-d').' '.$start, config('app.timezone'));
    }

    public function cancelDeadline(): ?Carbon
    {
        if (! $this->created_at) {
            return null;
        }

        $startsAt = $this->deliveryStartsAt();

        return $startsAt
            ? $startsAt->copy()->subHours(self::SCHEDULED_CANCEL_HOURS_BEFORE)
            : $this->created_at->copy()->addMinutes(self::INSTANT_CANCEL_MINUTES);
    }

    public function canBeCancelled(): bool
    {
        $deadline = $this->cancelDeadline();

        return $this->isPaid() && $deadline !== null && now()->lt($deadline);
    }

    public function cancelPolicyText(): string
    {
        return $this->delivery_schedule_type === 'scheduled'
            ? 'Scheduled order delivery slot shuru hone se '.self::SCHEDULED_CANCEL_HOURS_BEFORE.' ghante pehle tak cancel ho sakta hai.'
            : 'Instant order place hone ke '.self::INSTANT_CANCEL_MINUTES.' minute ke andar cancel ho sakta hai.';
    }

    public function refundableAmount(): float
    {
        return round((float) $this->total + (float) $this->wallet_amount, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
