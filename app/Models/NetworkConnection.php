<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkConnection extends Model
{
    protected $guarded = [];

    protected $hidden = ['configuration'];

    protected function casts(): array
    {
        return [
            'configuration' => 'encrypted:array',
            'last_tested_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
