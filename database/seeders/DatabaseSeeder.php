<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Site;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::query()->updateOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Mirev Access Demo', 'currency' => 'XAF'],
        );

        foreach ([
            ['name' => 'Pass Jour', 'price_minor' => 500, 'validity_minutes' => 1440, 'download_limit_kbps' => 5000],
            ['name' => 'Pass Semaine', 'price_minor' => 2500, 'validity_minutes' => 10080, 'download_limit_kbps' => 8000],
            ['name' => 'Résident', 'price_minor' => 8000, 'validity_minutes' => 43200, 'download_limit_kbps' => 10000],
        ] as $plan) {
            Plan::query()->updateOrCreate(
                ['site_id' => $site->id, 'name' => $plan['name']],
                $plan,
            );
        }
    }
}
