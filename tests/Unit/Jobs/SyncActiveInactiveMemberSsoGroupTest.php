<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoGroup;
use App\Jobs\SyncActiveInactiveMemberSsoGroup;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncActiveInactiveMemberSsoGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalizes_unset_member_parent_id_to_null(): void
    {
        config(['services.authentik.member_group_id' => null]);

        $activeGroup = Group::create(['name' => 'Active Member', 'is_managed' => true]);
        $inactiveGroup = Group::create(['name' => 'Inactive Member', 'is_managed' => true]);

        Bus::fake([CreateSsoGroup::class]);

        (new SyncActiveInactiveMemberSsoGroup)->handle();

        Bus::assertDispatched(
            CreateSsoGroup::class,
            fn (CreateSsoGroup $job) => $job->group->is($activeGroup) && $job->ssoParentIds === null,
        );
        Bus::assertDispatched(
            CreateSsoGroup::class,
            fn (CreateSsoGroup $job) => $job->group->is($inactiveGroup) && $job->ssoParentIds === null,
        );
    }
}
