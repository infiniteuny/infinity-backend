<?php

namespace Tests\Unit\Http\Resources\User;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    public function test_redacts_pii_when_unauthenticated_and_unredacts_on_default_or_semi_public_guard(): void
    {
        config([
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::forgetGuards();

        $user = (new User)->forceFill([
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Member',
            'email_address' => 'member@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'is_member' => true,
        ]);

        $unauth = (new UserResource($user))->toBaseArray(Request::create('/v1/users'));
        $this->assertSame('REDACTED', $unauth['email_address']);
        $this->assertSame('REDACTED', $unauth['phone_number']);
        $this->assertSame('REDACTED', $unauth['student_id']);

        Auth::guard('api_headers')->setUser($user);
        $authDefault = (new UserResource($user))->toBaseArray(Request::create('/v1/users'));
        $this->assertSame('member@infiniteuny.id', $authDefault['email_address']);
    }
}
