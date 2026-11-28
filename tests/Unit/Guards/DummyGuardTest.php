<?php

namespace Tests\Unit\Guards;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class DummyGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_handles_empty_or_invalid_dummy_user_id_safely_without_creating_db_rows(): void
    {
        config([
            'auth.defaults.guard' => 'dummy',
            'auth.dummy_user_id' => '',
        ]);
        Auth::forgetGuards();

        $user = Auth::guard('dummy')->user();

        $this->assertNotNull($user);
        $this->assertNotEmpty($user->id);
        $this->assertTrue(Str::isUuid((string) $user->major_id));
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('majors', 0);
    }
}
