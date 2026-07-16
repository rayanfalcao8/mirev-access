<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessGrant extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['authorized_at' => 'datetime'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
