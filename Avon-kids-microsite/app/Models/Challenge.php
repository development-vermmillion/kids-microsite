<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    protected $fillable = [
        'title', 'description', 'icon', 'color', 'reward_points', 'target_value',
        'unit', 'progress_label', 'metric', 'badge_id', 'starts_at', 'ends_at', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'float',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function riders(): BelongsToMany
    {
        return $this->belongsToMany(Rider::class)
            ->withPivot(['progress_value', 'completed_at'])
            ->withTimestamps();
    }

    /** Badge unlocked when a rider completes this challenge. */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Live and within its dates (if it has any): what riders see on the website. */
    public function scopeRunning(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->active()
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }

    public function getIsAutoAttribute(): bool
    {
        return $this->metric && $this->metric !== 'manual';
    }
}
