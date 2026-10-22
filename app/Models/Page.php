<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'name',
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

    public function contents(): HasMany
    {
        return $this->hasMany(PageContent::class);
    }
}
