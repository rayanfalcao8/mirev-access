<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($this->validity_minutes % 1440 === 0) {
            $days = intdiv($this->validity_minutes, 1440);

            return $days.' '.($days === 1 ? 'jour' : 'jours');
        }

        if ($this->validity_minutes % 60 === 0) {
            $hours = intdiv($this->validity_minutes, 60);

            return $hours.' h';
        }

        return $this->validity_minutes.' min';
    }
}
