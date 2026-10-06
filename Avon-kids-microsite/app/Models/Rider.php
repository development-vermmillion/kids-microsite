<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    protected $fillable = [
        'name', 'mobile', 'avatar', 'level', 'is_active',
    ];

    protected $hidden = ['otp'];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)
            ->withPivot(['progress_percent', 'unlocked_at'])
            ->withTimestamps();
    }

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot(['progress_value', 'completed_at'])
            ->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(RiderNotification::class)->latest();
    }

    /** "Alex Rider" -> "AR" */
    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return Uploads::url($this->avatar);
    }

    public function getLevelTitleAttribute(): string
    {
        return "Level {$this->level} Cyclist";
    }
}
