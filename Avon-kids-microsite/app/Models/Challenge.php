<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    protected $fillable = [
        'title', 'description', 'icon', 'color', 'reward_points', 'target_value',
        'unit', 'progress_label', 'starts_at', 'ends_at', 'is_active', 'sort_order',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
