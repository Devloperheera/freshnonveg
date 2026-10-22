<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvantageItem extends Model
{
    protected $fillable = [
        'icon',
        'title',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }
}
