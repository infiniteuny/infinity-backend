<?php

namespace Tests\Unit\Guards;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DummyGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_handles_empty_or_invalid_dummy_user_id_safely(): void
    {
        config([
            'auth.defaults.guard' => 'dummy',
            'auth.dummy_user_id' => '',
        ]);
        Auth::forgetGuards();

        $user = Auth::guard('dummy')->user();

        $this->assertNotNull($user);
        $this->assertNotEmpty($user->id);
    }
}
