<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateDocsCollection;
use App\Jobs\CreateDocsGroup;
use App\Jobs\CreateSsoGroup;
use App\Jobs\CreateSsoUser;
use App\Jobs\SyncMemberGroup;
use App\Jobs\UpdateDocsCollection;
use App\Jobs\UpdateDocsGroup;
use App\Jobs\UpdateSsoGroup;
use App\Jobs\UpdateSsoUser;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use ReflectionClass;
use Tests\TestCase;

class SyncMemberGroupTest extends TestCase
{
    public function test_unique_jobs_define_positive_ttl_and_lock_expiry_exceeding_worker_timeout(): void
    {
        $workerTimeout = (int) config('horizon.defaults.supervisor-1.timeout');
        $retryAfter = (int) config('queue.connections.redis.retry_after');

        $this->assertSame(180, $workerTimeout);
        $this->assertGreaterThan($workerTimeout, $retryAfter);

        $syncJob = new SyncMemberGroup;
        $this->assertGreaterThan(0, $syncJob->uniqueFor);

        foreach (glob(app_path('Jobs/*.php')) ?: [] as $file) {
            $class = 'App\\Jobs\\'.basename($file, '.php');
            $reflection = new ReflectionClass($class);
            $defaults = $reflection->getDefaultProperties();

            if (
                $reflection->implementsInterface(ShouldBeUnique::class)
                || $reflection->implementsInterface(ShouldBeUniqueUntilProcessing::class)
            ) {
                $this->assertArrayHasKey('uniqueFor', $defaults, "{$class} must define \$uniqueFor");
                $this->assertIsInt($defaults['uniqueFor'], "{$class}::\$uniqueFor must be an int");
                $this->assertGreaterThan(0, $defaults['uniqueFor'], "{$class}::\$uniqueFor must be > 0");
            }
        }

        $user = new User;
        $user->id = '01950000-0000-7000-8000-000000000010';
        $group = new Group(['name' => 'Check Group']);
        $group->id = '01950000-0000-7000-8000-000000000011';

        $modelCarryingJobs = [
            new CreateSsoUser($user),
            new UpdateSsoUser($user),
            new CreateSsoGroup($group),
            new UpdateSsoGroup($group),
            new CreateDocsGroup($group),
            new UpdateDocsGroup($group),
            new CreateDocsCollection($group, 'Col'),
            new UpdateDocsCollection($group, 'Col'),
        ];

        foreach ($modelCarryingJobs as $job) {
            $this->assertTrue($job->deleteWhenMissingModels, $job::class.' must set $deleteWhenMissingModels = true');

            foreach ($job->middleware() as $middleware) {
                if ($middleware instanceof WithoutOverlapping) {
                    $this->assertSame(240, $middleware->expiresAfter);
                    $this->assertGreaterThan($workerTimeout, $middleware->expiresAfter);
                }
            }
        }
    }
}
