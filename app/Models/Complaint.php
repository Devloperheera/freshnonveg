<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ];

    protected $fillable = [
        'user_id',
        'order_id',
        'subject',
        'message',
        'status',
        'admin_reply',
        'credited_amount',
        'resolved_at',
        'reply_emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'credited_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
            'reply_emailed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }
}
