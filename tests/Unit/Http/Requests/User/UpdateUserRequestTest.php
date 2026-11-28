<?php

namespace Tests\Unit\Http\Requests\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validated_strips_restricted_fields_without_permission_and_returns_scalars_with_permission(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        Permission::create(['name' => 'update-user']);
        Permission::create(['name' => 'manage-user-membership']);

        $limited = User::create([
            'name' => 'Limited',
            'username' => 'limited',
            'email_address' => 'limited@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $limited->givePermissionTo('update-user');

        $limitedRequest = UpdateUserRequest::create("/v1/users/{$limited->id}", 'PATCH', [
            'name' => 'Updated',
            'is_member' => true,
        ]);
        $limitedRequest->setContainer($this->app)->setRedirector($this->app['redirect']);
        $limitedRequest->setUserResolver(fn () => $limited);
        $limitedRequest->setValidator(Validator::make(
            ['name' => 'Updated', 'is_member' => true],
            ['name' => ['required', 'string'], 'is_member' => ['boolean']],
        ));

        $this->assertSame('Updated', $limitedRequest->validated('name'));
        $this->assertNull($limitedRequest->validated('is_member'));

        $limited->givePermissionTo('manage-user-membership');

        $adminRequest = UpdateUserRequest::create("/v1/users/{$limited->id}", 'PATCH', [
            'is_member' => 'true',
        ]);
        $adminRequest->setContainer($this->app)->setRedirector($this->app['redirect']);
        $adminRequest->setUserResolver(fn () => $limited);
        $adminRequest->validateResolved();

        $this->assertTrue($adminRequest->validated('is_member'));
    }
}
