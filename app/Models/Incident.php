<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['context' => 'array', 'resolved_at' => 'datetime'];
    }

    public static function report(
        string $fingerprint,
        string $type,
        string $title,
        string $message,
        array $context = [],
        string $severity = 'warning',
    ): self {
        return self::query()->updateOrCreate(
            ['fingerprint' => $fingerprint],
            [
                'type' => $type,
                'severity' => $severity,
                'status' => 'open',
                'title' => $title,
                'message' => $message,
                'context' => $context,
                'resolved_at' => null,
            ],
        );
    }
}
