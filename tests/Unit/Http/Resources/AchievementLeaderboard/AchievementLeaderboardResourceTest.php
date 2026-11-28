<?php

namespace Tests\Unit\Http\Resources\AchievementLeaderboard;

use App\Http\Resources\AchievementLeaderboard\AchievementLeaderboardResource;
use App\Models\AchievementLeaderboard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AchievementLeaderboardResourceTest extends TestCase
{
    public function test_redacts_nested_user_pii_when_unauthenticated_and_unredacts_when_authenticated(): void
    {
        config([
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::forgetGuards();

        $user = (new User)->forceFill([
            'id' => '00000000-0000-0000-0000-000000000001',
            'email_address' => 'member@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'is_member' => true,
        ]);
        $leaderboard = (new AchievementLeaderboard)->forceFill([
            'id' => '00000000-0000-0000-0000-000000000099',
            'user_id' => $user->id,
            'year' => 2026,
            'total_points' => 150,
        ]);
        $leaderboard->setRelation('user', $user);

        $unauth = (new AchievementLeaderboardResource($leaderboard))->toBaseArray(Request::create('/v1/leaderboards/achievements/2026'));
        $this->assertSame('REDACTED', $unauth['user']['email_address']);

        Auth::guard('api_token')->setUser($user);
        $auth = (new AchievementLeaderboardResource($leaderboard))->toBaseArray(Request::create('/v1/leaderboards/achievements/2026'));
        $this->assertSame('member@infiniteuny.id', $auth['user']['email_address']);
    }
}
