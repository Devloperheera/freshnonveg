<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PushNotification extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SENT,
        self::STATUS_FAILED,
    ];

    protected $fillable = [
        'notification_group_id',
        'title',
        'message',
        'image',
        'click_action',
        'status',
        'recipients_count',
        'delivered_count',
        'scheduled_at',
        'sent_at',
        'error_summary',
    ];

    protected function casts(): array
    {
        return [
            'recipients_count' => 'integer',
            'delivered_count' => 'integer',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(NotificationGroup::class, 'notification_group_id');
    }

    public function imageUrl(): ?string
    {
        return StorageUrl::forPath($this->image);
    }

    public function deliveryLabel(): string
    {
        if ($this->recipients_count <= 0) {
            return '—';
        }

        $pct = (int) round(($this->delivered_count / $this->recipients_count) * 100);

        return $pct.'%';
    }

    public function deleteImageFile(): void
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
