<?php

namespace Tests\Unit\Traits;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use App\Traits\HasQueryFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Tests\TestCase;

class HasQueryFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_case_insensitive_filter_matches_trimmed_exact_value_and_rejects_partial_or_batch_values(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $alice = User::create([
            'name' => 'Alice',
            'username' => 'alice',
            'email_address' => 'Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        User::create([
            'name' => 'Malice',
            'username' => 'malice',
            'email_address' => 'malice.Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6282222222222',
            'student_id' => '9924520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $subject = new class
        {
            use HasQueryFilters;

            public function run(array $query): array
            {
                $request = Request::create('/v1/users', 'GET', $query);

                return QueryBuilder::for(User::class, $request)
                    ->allowedFilters($this->exactCaseInsensitiveFilter('email_address'))
                    ->pluck('id')
                    ->all();
            }
        };

        $this->assertSame([$alice->id], $subject->run(['filters' => ['email_address' => '  alice.putri@student.uny.ac.id  ']]));
        $this->assertSame([], $subject->run(['filters' => ['email_address' => '@student.uny.ac.id']]));
        $this->assertSame([], $subject->run(['filters' => ['email_address' => 'alice.putri@student.uny.ac.id,malice.alice.putri@student.uny.ac.id']]));
        $this->assertSame([], $subject->run(['filters' => ['email_address' => ['alice.putri@student.uny.ac.id']]]));
        $this->assertSame([], $subject->run(['filters' => ['email_address' => '   ']]));
    }
}
