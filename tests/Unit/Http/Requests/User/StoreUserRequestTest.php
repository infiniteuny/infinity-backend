<?php

namespace Tests\Unit\Http\Requests\User;

use App\Http\Requests\User\StoreUserRequest;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validated_supports_scalar_keys_and_strips_restricted_membership_fields_without_permission(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'manage-user-membership']);

        $editor = User::create([
            'name' => 'Editor',
            'username' => 'editor',
            'email_address' => 'editor@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $editor->givePermissionTo('create-user');

        $request = StoreUserRequest::create('/v1/users', 'POST', [
            'name' => 'New Member',
            'username' => 'newmember',
            'email_address' => 'newmember@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => ['github' => 'https://github.com/infiniteuny'],
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_member' => true,
            'is_extraordinary' => true,
        ]);
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $request->setUserResolver(fn () => $editor);
        $request->validateResolved();

        $this->assertSame('New Member', $request->validated('name'));
        $this->assertSame('https://github.com/infiniteuny', $request->validated('links.github'));
        $this->assertNull($request->validated('is_member'));
        $this->assertSame('fallback', $request->validated('is_member', 'fallback'));
        $this->assertArrayNotHasKey('is_member', $request->validated());
        $this->assertArrayNotHasKey('is_extraordinary', $request->validated());
    }
}
