<?php

namespace Tests\Unit\Http\Resources\Achievement;

use App\Http\Resources\Achievement\AchievementResource;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AchievementResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_redacts_team_id_when_unauthenticated_and_unredacts_when_authenticated(): void
    {
        config([
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::forgetGuards();

        $achievement = (new Achievement)->forceFill([
            'id' => '00000000-0000-0000-0000-000000000010',
            'team_id' => '00000000-0000-0000-0000-000000000020',
            'description' => 'Winner',
        ]);

        $unauth = (new AchievementResource($achievement))->toBaseArray(Request::create('/v1/achievements'));
        $this->assertSame('REDACTED', $unauth['team_id']);

        Auth::guard('api_headers')->setUser((new User)->forceFill(['id' => '00000000-0000-0000-0000-000000000001']));
        $auth = (new AchievementResource($achievement))->toBaseArray(Request::create('/v1/achievements'));
        $this->assertSame('00000000-0000-0000-0000-000000000020', $auth['team_id']);
    }
}
