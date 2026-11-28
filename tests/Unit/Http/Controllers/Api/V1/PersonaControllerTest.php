<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use RuntimeException;
use Tests\TestCase;

class PersonaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_db_update_preserves_old_blob_and_cleans_up_newly_uploaded_blob(): void
    {
        LaravelStorage::fake('public');
        Bus::fake([DeleteBlob::class]);

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
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $admin->givePermissionTo(Permission::create(['name' => 'update-persona', 'guard_name' => 'api']));

        $oldManifest = '{"disk":"public","path":"personas/logos","name":"old-logo.png","visibility":"public"}';
        $persona = Persona::create([
            'name' => 'Frontend Developer',
            'priority' => 1,
            'description' => 'Builds UIs',
            'logo' => $oldManifest,
        ]);

        $eventName = 'eloquent.updating: '.Persona::class;
        Event::listen($eventName, function (): never {
            throw new RuntimeException('Simulated database failure during update');
        });

        try {
            $this->actingAs($admin, 'api_headers')
                ->put("/v1/personas/{$persona->id}", [
                    'logo' => UploadedFile::fake()->image('new-logo.png'),
                ], ['Accept' => 'application/json'])
                ->assertStatus(500);
        } finally {
            Event::forget($eventName);
        }

        $persona->refresh();
        $this->assertSame($oldManifest, $persona->getRawOriginal('logo'));

        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest !== $oldManifest);
    }
}
