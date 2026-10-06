<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Badge extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'color', 'image',
        'show_on_home', 'show_in_trophy_room', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'show_on_home' => 'boolean',
            'show_in_trophy_room' => 'boolean',
        ];
    }

    public function riders(): BelongsToMany
    {
        return $this->belongsToMany(Rider::class)
            ->withPivot(['progress_percent', 'unlocked_at'])
            ->withTimestamps();
    }

    /** Accepts either a file name inside public/frontend/images or a full URL. */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Str::startsWith($this->image, ['http://', 'https://'])
            ? $this->image
            : asset('frontend/images/'.$this->image);
    }
}
