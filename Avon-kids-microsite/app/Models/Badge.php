<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Uploads;

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

    /** Accepts a file name inside public/frontend/images, an uploads/ path, or a full URL. */
    public function getImageUrlAttribute(): ?string
    {
        return Uploads::url($this->image);
    }
}
