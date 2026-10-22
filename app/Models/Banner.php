<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\StorageUrl;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'name',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function imageUrl(): string
    {
        return StorageUrl::forPath($this->image) ?? '';
    }

    public function deleteImageFile(): void
    {
        if ($this->image && ! str_starts_with($this->image, 'http')) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
