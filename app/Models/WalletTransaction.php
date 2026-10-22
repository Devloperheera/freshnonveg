<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    /** @var array<string, string> */
    public const SOURCES = [
        'direct' => 'Direct (admin)',
        'complaint' => 'Complaint',
        'refund' => 'Order refund',
        'used' => 'Used in order',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'complaint_id',
        'order_id',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function isCredit(): bool
    {
        return in_array($this->type, ['credit', 'refund'], true);
    }

    public function source(): string
    {
        return match (true) {
            $this->type === 'debit' => 'used',
            $this->type === 'refund' => 'refund',
            $this->complaint_id !== null => 'complaint',
            default => 'direct',
        };
    }

    public function scopeSource(Builder $query, string $source): Builder
    {
        return match ($source) {
            'direct' => $query->where('type', 'credit')->whereNull('complaint_id'),
            'complaint' => $query->where('type', 'credit')->whereNotNull('complaint_id'),
            'refund' => $query->where('type', 'refund'),
            'used' => $query->where('type', 'debit'),
            'credits' => $query->whereIn('type', ['credit', 'refund']),
            default => $query,
        };
    }
}
