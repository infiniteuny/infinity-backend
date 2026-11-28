<?php

namespace Tests\Unit\Traits;

use App\Models\AchievementLeaderboard;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use App\Traits\HasPagination;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;
use Tests\TestCase;
use UnexpectedValueException;

class HasPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_per_page_clamps_integers_to_1_through_500_and_defaults_invalid_inputs_to_10(): void
    {
        $helper = new class
        {
            use HasPagination;

            public function check(array $query): int
            {
                return $this->resolvePerPage(Request::create('/v1/users', 'GET', $query));
            }
        };

        $this->assertSame(10, $helper->check([]));
        $this->assertSame(10, $helper->check(['per_page' => '']));
        $this->assertSame(10, $helper->check(['per_page' => 'abc']));
        $this->assertSame(10, $helper->check(['per_page' => ['20']]));
        $this->assertSame(1, $helper->check(['per_page' => '0']));
        $this->assertSame(1, $helper->check(['per_page' => '-5']));
        $this->assertSame(25, $helper->check(['per_page' => '25']));
        $this->assertSame(500, $helper->check(['per_page' => '500']));
        $this->assertSame(500, $helper->check(['per_page' => '100000']));
    }

    public function test_cursor_paginate_with_tie_breaker_preserves_tied_rows_and_sparse_fields_and_appends(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        try {
            $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
            $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
            $major = Major::create([
                'code' => 'PTI',
                'name' => 'Pendidikan Teknik Informatika',
                'degree_id' => $degree->id,
                'faculty_id' => $faculty->id,
            ]);

            $u1 = User::create([
                'name' => 'Same Name',
                'username' => 'user1',
                'email_address' => 'u1@infiniteuny.id',
                'phone_number' => '+6281111111101',
                'student_id' => '24520241001',
                'major_id' => $major->id,
                'links' => (object) [],
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_member' => true,
                'is_extraordinary' => false,
            ]);
            $u2 = User::create([
                'name' => 'Same Name',
                'username' => 'user2',
                'email_address' => 'u2@infiniteuny.id',
                'phone_number' => '+6281111111102',
                'student_id' => '24520241002',
                'major_id' => $major->id,
                'links' => (object) [],
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'is_member' => true,
                'is_extraordinary' => false,
            ]);
            $u3 = User::create([
                'name' => 'Same Name',
                'username' => 'user3',
                'email_address' => 'u3@infiniteuny.id',
                'phone_number' => '+6281111111103',
                'student_id' => '24520241003',
                'major_id' => $major->id,
                'links' => (object) [],
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_member' => true,
                'is_extraordinary' => false,
            ]);

            $helper = new class
            {
                use HasPagination;

                public function paginateUsers(array $params): CursorPaginator
                {
                    $request = Request::create('/v1/users', 'GET', $params);
                    $qb = QueryBuilder::for(User::class, $request)
                        ->allowedFields('id', 'name', 'username', 'is_member', 'start_date', 'end_date')
                        ->allowedSorts('id', 'name', 'username')
                        ->defaultSorts('name');

                    return $this->cursorPaginateWithTieBreaker($qb, $request);
                }

                public function paginateRaw(EloquentBuilder $builder, array $params, array $uniqueColumns = ['id']): CursorPaginator
                {
                    return $this->cursorPaginateWithTieBreaker(
                        $builder,
                        Request::create('/v1/test', 'GET', $params),
                        $uniqueColumns,
                    );
                }
            };

            $seenUsernames = [];
            $activeMap = [];
            $cursor = null;

            do {
                $params = ['fields' => 'username', 'sorts' => 'name', 'per_page' => '1'];
                if ($cursor !== null) {
                    $params['cursor'] = $cursor;
                }

                $page = $helper->paginateUsers($params);
                foreach ($page->items() as $user) {
                    $serialized = $user->toArray();
                    $this->assertArrayHasKey('username', $serialized);
                    $this->assertArrayHasKey('is_active', $serialized);
                    $this->assertArrayNotHasKey('id', $serialized);
                    $this->assertArrayNotHasKey('name', $serialized);
                    $this->assertArrayNotHasKey('is_member', $serialized);
                    $this->assertArrayNotHasKey('start_date', $serialized);
                    $this->assertArrayNotHasKey('end_date', $serialized);

                    $seenUsernames[] = $serialized['username'];
                    $activeMap[$serialized['username']] = $serialized['is_active'];
                }

                $cursor = $page->nextCursor()?->encode();
            } while ($cursor !== null);

            $this->assertCount(3, $seenUsernames);
            $this->assertEqualsCanonicalizing(['user1', 'user2', 'user3'], $seenUsernames);
            $this->assertTrue($activeMap['user1']);
            $this->assertFalse($activeMap['user2']);
            $this->assertTrue($activeMap['user3']);

            AchievementLeaderboard::create(['user_id' => $u1->id, 'year' => 2025, 'total_points' => 10]);
            AchievementLeaderboard::create(['user_id' => $u2->id, 'year' => 2026, 'total_points' => 20]);

            $distinctBuilder = AchievementLeaderboard::query()
                ->selectRaw('DISTINCT year')
                ->orderByDesc('year');
            $distinctPage = $helper->paginateRaw($distinctBuilder, ['per_page' => '10'], ['year']);
            $this->assertCount(2, $distinctPage->items());
            $this->assertCount(1, $distinctBuilder->getQuery()->orders);

            $this->expectException(UnexpectedValueException::class);
            $helper->paginateUsers(['cursor' => 'not-valid-base64-cursor']);
        } finally {
            Carbon::setTestNow();
        }
    }
}
