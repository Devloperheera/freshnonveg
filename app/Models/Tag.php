<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'status'];

    protected function casts(): array
    {
        return ['status' => 'integer'];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
