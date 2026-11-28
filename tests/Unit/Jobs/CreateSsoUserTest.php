<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoUser;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\User;
use App\Services\AuthentikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateSsoUserTest extends TestCase
{
    use RefreshDatabase;

    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    public function test_handles_prelinked_404_adopts_exact_email_with_full_payload_saves_quietly_and_touches_groups(): void
    {
        config([
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => 'ak_test_token',
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $alice = User::create([
            'name' => 'Alice Updated',
            'username' => 'aliceputri',
            'email_address' => 'alice@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $pastTime = Carbon::now()->subHours(2)->startOfSecond();
        $memberGroup = Group::create(['name' => 'Member', 'is_managed' => true]);
        $memberGroup->users()->attach($alice->id);
        Group::whereKey($memberGroup->id)->update(['updated_at' => $pastTime]);
        User::whereKey($alice->id)->update(['updated_at' => $pastTime]);
        $alice->refresh();

        User::withoutTimestamps(function () use ($alice) {
            $alice->sso_id = '999';
            $alice->saveQuietly();
        });

        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/users/999/' => Http::response(['detail' => 'Not found.'], 404),
            self::AUTHENTIK_URL.'/api/v3/core/users/*' => function (Request $request) use ($alice) {
                if ($request->method() === 'GET') {
                    return Http::response([
                        'results' => [
                            ['pk' => 10, 'username' => 'other', 'name' => 'Other', 'email' => 'prefix_alice@infiniteuny.id', 'is_active' => false],
                            ['pk' => 42, 'username' => 'old', 'name' => 'Old Name', 'email' => 'ALICE@infiniteuny.id', 'is_active' => false],
                        ],
                    ]);
                }

                $this->assertArrayNotHasKey('pk', $request->data());
                $this->assertSame('Alice Updated', $request['name']);
                $this->assertSame($alice->username, $request['username']);
                $this->assertTrue($request['is_active']);

                return Http::response([
                    'pk' => 42,
                    'username' => $alice->username,
                    'name' => 'Alice Updated',
                    'email' => 'alice@infiniteuny.id',
                    'is_active' => true,
                ]);
            },
        ]);

        (new CreateSsoUser($alice))->handle(app(AuthentikService::class));

        $alice->refresh();
        $this->assertSame('42', $alice->sso_id);
        $this->assertTrue($alice->updated_at->equalTo($pastTime));
        $this->assertTrue($memberGroup->fresh()->updated_at->gt($pastTime));
    }
}
