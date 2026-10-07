<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderNotification extends Model
{
    protected $fillable = [
        'rider_id', 'title', 'message', 'icon', 'color', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function getIsUnreadAttribute(): bool
    {
        return $this->read_at === null;
    }

    /** "2 hours ago", "Yesterday", "2 days ago" */
    public function getTimeLabelAttribute(): string
    {
        return $this->created_at->isYesterday()
            ? 'Yesterday'
            : $this->created_at->diffForHumans();
    }
}
