<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoGroup;
use App\Jobs\SyncCustomSsoGroups;
use App\Jobs\UpdateSsoGroup;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncCustomSsoGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_create_and_update_only_for_unsynced_or_stale_custom_groups(): void
    {
        Bus::fake([CreateSsoGroup::class, UpdateSsoGroup::class]);

        $managedUnsynced = Group::create(['name' => 'Managed Cohort', 'is_managed' => true]);

        $customUnsynced = Group::create(['name' => 'Working Group Alpha', 'is_managed' => false]);

        $customStale = Group::create(['name' => 'Working Group Beta', 'is_managed' => false]);
        Group::withoutTimestamps(function () use ($customStale) {
            $customStale->sso_id = 'sso-wg-beta';
            $customStale->sso_last_synced_at = Carbon::now()->subHour();
            $customStale->saveQuietly();
        });

        $customUpToDate = Group::create(['name' => 'Working Group Gamma', 'is_managed' => false]);
        $past = Carbon::now()->subHours(2)->startOfSecond();
        Group::whereKey($customUpToDate->id)->update(['updated_at' => $past]);
        Group::withoutTimestamps(function () use ($customUpToDate) {
            $customUpToDate->sso_id = 'sso-wg-gamma';
            $customUpToDate->sso_last_synced_at = Carbon::now()->subMinute();
            $customUpToDate->saveQuietly();
        });

        $job = new SyncCustomSsoGroups;
        $this->assertSame(1800, $job->uniqueFor);
        $this->assertSame(3, $job->tries);
        $this->assertSame(10, $job->backoff);

        $job->handle();

        Bus::assertDispatched(CreateSsoGroup::class, fn (CreateSsoGroup $j) => $j->group->is($customUnsynced));
        Bus::assertDispatched(UpdateSsoGroup::class, fn (UpdateSsoGroup $j) => $j->group->is($customStale));
        Bus::assertNotDispatched(CreateSsoGroup::class, fn (CreateSsoGroup $j) => $j->group->is($managedUnsynced));
        Bus::assertNotDispatched(UpdateSsoGroup::class, fn (UpdateSsoGroup $j) => $j->group->is($customUpToDate));
    }
}
