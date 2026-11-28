<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Models\Config;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ConfigControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hides_private_configs_when_unauthenticated_and_exposes_them_via_semi_public_guard(): void
    {
        config([
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::shouldUse('api_headers');

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email_address' => 'admin@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        Config::create([
            'key' => 'public_banner',
            'value' => 'Welcome',
            'type' => 'STRING',
            'is_private' => false,
        ]);
        Config::create([
            'key' => 'allow_reregistration',
            'value' => 'true',
            'type' => 'BOOLEAN',
            'is_private' => true,
        ]);

        $this->getJson('/v1/configs')
            ->assertOk()
            ->assertJsonCount(1, 'data.configs')
            ->assertJsonPath('data.configs.0.key', 'public_banner');

        $this->getJson('/v1/configs/public_banner')
            ->assertOk()
            ->assertJsonPath('data.config.value', 'Welcome');

        $this->getJson('/v1/configs/allow_reregistration')
            ->assertNotFound();

        $this->getJson('/v1/configs?filters[is_private]=1')->assertStatus(400);
        $this->getJson('/v1/configs?sorts=is_private')->assertStatus(400);

        $this->actingAs($admin, 'api_token');
        $this->assertNull(Auth::guard('api_headers')->user());

        $this->getJson('/v1/configs?filters[is_private]=1&sorts=is_private')
            ->assertOk()
            ->assertJsonCount(1, 'data.configs')
            ->assertJsonPath('data.configs.0.key', 'allow_reregistration');

        $this->getJson('/v1/configs/allow_reregistration')
            ->assertOk()
            ->assertJsonPath('data.config.value', 'true');
    }
}
