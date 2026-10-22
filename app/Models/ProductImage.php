<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\StorageUrl;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return StorageUrl::forPath($this->path) ?? '';
    }

    public function dataUri(): ?string
    {
        if (! $this->path || str_starts_with($this->path, 'http') || ! Storage::disk('public')->exists($this->path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($this->path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($this->path));
    }

    public function deleteFile(): void
    {
        if ($this->path && ! str_starts_with($this->path, 'http')) {
            Storage::disk('public')->delete($this->path);
        }
    }
}
