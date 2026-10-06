<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ride extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'rider_id', 'title', 'ride_date', 'ride_time', 'distance_km',
        'duration_minutes', 'proof_image', 'status', 'rejection_reason', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'ride_date' => 'date',
            'distance_km' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function getProofUrlAttribute(): ?string
    {
        return Uploads::url($this->proof_image);
    }

    /** "1h 20m", "35m", "2h" */
    public function getDurationLabelAttribute(): ?string
    {
        if ($this->duration_minutes === null) {
            return null;
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours === 0) {
            return "{$minutes}m";
        }

        return $minutes ? "{$hours}h {$minutes}m" : "{$hours}h";
    }

    /** Text shown in the status pill on the progress page. */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_REJECTED => $this->rejection_reason ?: 'Rejected',
            default => 'Pending Review',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VERIFIED => 'check_circle',
            self::STATUS_REJECTED => 'error',
            default => 'hourglass_empty',
        };
    }
}
