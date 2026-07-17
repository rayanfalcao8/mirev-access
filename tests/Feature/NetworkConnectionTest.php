<?php

namespace Tests\Feature;

use App\Domain\Access\Actions\TestNetworkConnection;
use App\Models\NetworkConnection;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NetworkConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_network_configuration_is_encrypted_and_testable(): void
    {
        $site = Site::query()->create([
            'name' => 'Mirev Network',
            'slug' => 'mirev-network',
            'currency' => 'XAF',
        ]);

        $connection = NetworkConnection::query()->create([
            'site_id' => $site->id,
            'provider' => 'fake',
            'configuration' => [
                'endpoint' => 'https://controller.example.com',
                'secret' => 'super-secret',
            ],
        ]);

        $stored = DB::table('network_connections')->where('id', $connection->id)->value('configuration');

        $this->assertStringNotContainsString('super-secret', $stored);
        $this->assertTrue(app(TestNetworkConnection::class)->execute($connection));

        $connection->refresh();
        $this->assertSame('connected', $connection->status);
        $this->assertNotNull($connection->last_tested_at);
        $this->assertNull($connection->last_error);
    }

    public function test_each_site_has_only_one_network_connection(): void
    {
        $site = Site::query()->create([
            'name' => 'Unique Network',
            'slug' => 'unique-network',
            'currency' => 'XAF',
        ]);

        NetworkConnection::query()->create([
            'site_id' => $site->id,
            'provider' => 'fake',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        NetworkConnection::query()->create([
            'site_id' => $site->id,
            'provider' => 'fake',
        ]);
    }
}
