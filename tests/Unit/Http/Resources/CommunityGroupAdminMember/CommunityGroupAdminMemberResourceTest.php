<?php

namespace Tests\Unit\Http\Resources\CommunityGroupAdminMember;

use App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CommunityGroupAdminMemberResourceTest extends TestCase
{
    public function test_redacts_pii_when_unauthenticated_and_unredacts_when_authenticated(): void
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

        $unauth = (new CommunityGroupAdminMemberResource($user))->toBaseArray(Request::create('/v1/community-group-admin-members'));
        $this->assertSame('REDACTED', $unauth['email_address']);

        Auth::guard('api_headers')->setUser($user);
        $auth = (new CommunityGroupAdminMemberResource($user))->toBaseArray(Request::create('/v1/community-group-admin-members'));
        $this->assertSame('member@infiniteuny.id', $auth['email_address']);
    }
}
