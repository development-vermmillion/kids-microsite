<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'color', 'image', 'metric', 'target_value',
        'show_on_home', 'show_in_trophy_room', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'float',
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

    public function getIsAutoAttribute(): bool
    {
        return $this->metric && $this->metric !== 'manual' && $this->target_value > 0;
    }

    /** Accepts a file name inside public/frontend/images, an uploads/ path, or a full URL. */
    public function getImageUrlAttribute(): ?string
    {
        return Uploads::url($this->image);
    }
}
