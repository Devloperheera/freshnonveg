<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'star_rating',
        'short_description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'star_rating' => 'integer',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function starsHtml(): string
    {
        $rating = max(1, min(5, (int) $this->star_rating));
        $filled = str_repeat('★', $rating);
        $empty = str_repeat('☆', 5 - $rating);

        return $filled.$empty;
    }
}
