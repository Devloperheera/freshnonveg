<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageContent extends Model
{
    protected $fillable = [
        'page_id',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'page_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
