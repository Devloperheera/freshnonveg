<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteStat extends Model
{
    protected $fillable = [
        'value',
        'label',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }
}
