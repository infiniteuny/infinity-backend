<?php

namespace Tests\Unit\Models;

use App\Models\GroupIntegration;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_group_and_casts_sync_timestamps(): void
    {
        $integration = GroupIntegration::factory()->create([
            'docs_group_last_synced_at' => '2026-06-01 10:00:00',
            'docs_collection_last_synced_at' => '2026-06-01 10:05:00',
        ])->fresh();

        $this->assertNotNull($integration->group);
        $this->assertInstanceOf(CarbonInterface::class, $integration->docs_group_last_synced_at);
        $this->assertInstanceOf(CarbonInterface::class, $integration->docs_collection_last_synced_at);
    }
}
