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

    /** Same defaults as the database, so new riders are active straight away. */
    protected $attributes = [
        'level' => 1,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
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

    /** Photo URL, or a generated initials circle when the rider has no photo. */
    public function getAvatarOrPlaceholderAttribute(): string
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="80" height="80" rx="40" fill="#ffdad4"/>'
            .'<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="700" fill="#bc0100">'
            .e($this->initials).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function getLevelTitleAttribute(): string
    {
        return "Level {$this->level} Cyclist";
    }
}
