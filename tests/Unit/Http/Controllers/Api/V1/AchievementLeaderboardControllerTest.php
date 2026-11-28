<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Models\AchievementLeaderboard;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AchievementLeaderboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
    }

    public function test_show_uses_exact_user_id_filter_and_preserves_tied_scores_across_cursor_pages(): void
    {
        $u1 = $this->createUser('User 1', 'user1', 'u1@infiniteuny.id', '24520241001', '+6281111111101');
        $u2 = $this->createUser('User 2', 'user2', 'u2@infiniteuny.id', '24520241002', '+6281111111102');
        $u3 = $this->createUser('User 3', 'user3', 'u3@infiniteuny.id', '24520241003', '+6281111111103');
        $u4 = $this->createUser('User 4', 'user4', 'u4@infiniteuny.id', '24520241004', '+6281111111104');

        AchievementLeaderboard::create(['user_id' => $u1->id, 'year' => 2026, 'total_points' => 100]);
        AchievementLeaderboard::create(['user_id' => $u2->id, 'year' => 2026, 'total_points' => 50]);
        AchievementLeaderboard::create(['user_id' => $u3->id, 'year' => 2026, 'total_points' => 50]);
        AchievementLeaderboard::create(['user_id' => $u4->id, 'year' => 2026, 'total_points' => 50]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $filteredResponse = $this->getJson("/v1/leaderboards/achievements/2026?filters[user_id]={$u2->id}");
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $filteredResponse->assertOk();
        $this->assertCount(1, $filteredResponse->json('data.achievement_leaderboards'));
        $this->assertSame($u2->id, $filteredResponse->json('data.achievement_leaderboards.0.user_id'));

        foreach ($queries as $sql) {
            $this->assertStringNotContainsString('lower(', strtolower($sql));
        }

        $partialUuid = substr($u2->id, 0, 8);
        $this->getJson("/v1/leaderboards/achievements/2026?filters[user_id]={$partialUuid}")
            ->assertOk()
            ->assertJsonCount(0, 'data.achievement_leaderboards');

        $collectedUserIds = [];
        $cursor = null;

        do {
            $url = '/v1/leaderboards/achievements/2026?per_page=2&sorts=-total_points'
                .($cursor ? "&cursor={$cursor}" : '');
            $page = $this->getJson($url)->assertOk();

            foreach ($page->json('data.achievement_leaderboards') as $row) {
                $collectedUserIds[] = $row['user_id'];
            }

            $cursor = $page->json('data.meta.next_cursor');
        } while ($cursor !== null);

        $this->assertCount(4, $collectedUserIds);
        $this->assertEqualsCanonicalizing([$u1->id, $u2->id, $u3->id, $u4->id], $collectedUserIds);
    }

    public function test_show_accepts_any_digit_count_year_and_returns_404_for_non_digits(): void
    {
        $this->getJson('/v1/leaderboards/achievements/99')
            ->assertOk()
            ->assertJsonPath('data.achievement_leaderboards', []);

        $this->getJson('/v1/leaderboards/achievements/10000')
            ->assertOk()
            ->assertJsonPath('data.achievement_leaderboards', []);

        $this->getJson('/v1/leaderboards/achievements/99999999999999999999')
            ->assertOk()
            ->assertJsonPath('data.achievement_leaderboards', []);

        $this->getJson('/v1/leaderboards/achievements/not-a-year')->assertNotFound();
        $this->getJson('/v1/leaderboards/achievements/-2026')->assertNotFound();
    }

    private function createUser(
        string $name,
        string $username,
        string $email,
        string $studentId,
        string $phone,
    ): User {
        return User::create([
            'name' => $name,
            'username' => $username,
            'email_address' => $email,
            'phone_number' => $phone,
            'student_id' => $studentId,
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
