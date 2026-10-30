<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class NotificationGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_group_user')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function iconUrl(): ?string
    {
        if (empty($this->icon)) {
            return null;
        }

        return StorageUrl::forPath($this->icon);
    }

    public function deleteIconFile(): void
    {
        if ($this->icon && Storage::disk('public')->exists($this->icon)) {
            Storage::disk('public')->delete($this->icon);
        }
    }
}
