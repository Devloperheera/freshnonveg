<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    public const LABELS = ['Home', 'Work', 'Other'];

    protected $fillable = [
        'user_id',
        'label',
        'phone',
        'pincode',
        'state',
        'city',
        'post_office',
        'address',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** One-line address for cards / dropdowns. */
    public function fullLine(): string
    {
        return implode(', ', array_filter([
            trim((string) $this->address),
            trim((string) $this->city),
            trim((string) $this->state),
            trim((string) $this->pincode),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'phone' => $this->phone,
            'pincode' => $this->pincode,
            'state' => $this->state,
            'city' => $this->city,
            'post_office' => $this->post_office,
            'address' => $this->address,
            'full_address' => $this->fullLine(),
            'is_default' => (bool) $this->is_default,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
