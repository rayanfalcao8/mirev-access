<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    protected $guarded = [];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function networkConnection(): HasOne
    {
        return $this->hasOne(NetworkConnection::class);
    }

    public function getPortalUrlAttribute(): string
    {
        return route('client.portal', $this);
    }
}
