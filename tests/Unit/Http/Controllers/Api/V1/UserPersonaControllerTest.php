<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class UserPersonaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_http_201_created(): void
    {
        Bus::fake();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $admin->givePermissionTo(Permission::create([
            'name' => 'create-user-persona',
            'guard_name' => 'api',
        ]));

        $targetUser = User::create([
            'name' => 'Member',
            'username' => 'memberuser',
            'email_address' => 'member@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $persona = Persona::create([
            'name' => 'Architect',
            'priority' => 1,
            'description' => 'System architect',
            'logo' => '{"disk":"public","path":"personas","name":"arch.png","visibility":"public"}',
        ]);

        $this->actingAs($admin, 'api_headers')
            ->postJson("/v1/users/{$targetUser->id}/personas", [
                'persona_id' => $persona->id,
            ])
            ->assertCreated();
    }
}
