<?php

namespace Tests\Unit\Models;

use App\Models\CommunityGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_priority_to_integer_and_is_active_to_boolean(): void
    {
        $communityGroup = CommunityGroup::create([
            'name' => 'Security',
            'priority' => '3',
            'description' => 'Application security',
            'logo' => '{"disk":"public","path":"cg","name":"sec.png","visibility":"public"}',
            'is_active' => 1,
        ])->fresh();

        $this->assertSame(3, $communityGroup->priority);
        $this->assertSame(true, $communityGroup->is_active);
    }
}
