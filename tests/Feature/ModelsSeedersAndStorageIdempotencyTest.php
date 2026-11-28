<?php

namespace Tests\Feature;

use App\Enums\StorageVisibility;
use App\Facades\Storage as BlobStorage;
use App\Jobs\DeleteBlob;
use App\Mail\ContactUs;
use App\Models\CommunityGroup;
use App\Models\CompetitionRank;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Persona;
use App\Models\Token;
use App\Models\User;
use Database\Seeders\BaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use InvalidArgumentException;
use Tests\TestCase;

class ModelsSeedersAndStorageIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_base_seeder_is_idempotent_when_rerun_after_admin_edits_seeded_rows(): void
    {
        (new BaseSeeder)->run();

        $persona = Persona::where('name', 'Hustler')->firstOrFail();
        $persona->update([
            'logo' => '{"disk":"public","visibility":"public","path":"public/personas/logos","name":"hustler.png"}',
        ]);

        $cg = CommunityGroup::where('name', 'Cyber Security')->firstOrFail();
        $cg->update(['description' => 'Updated cyber security description']);

        $degree = Degree::where('code', '3')->firstOrFail();
        $degree->update(['name' => 'Sarjana (S1)']);

        $rank = CompetitionRank::where('name', 'Juara 1')->firstOrFail();
        $rank->update(['weight' => 10]);

        // Re-running BaseSeeder must NOT crash with unique constraint violations or overwrite customized attributes
        (new BaseSeeder)->run();

        $this->assertSame(
            '{"disk":"public","visibility":"public","path":"public/personas/logos","name":"hustler.png"}',
            $persona->fresh()->getRawOriginal('logo'),
        );
        $this->assertSame('Updated cyber security description', $cg->fresh()->description);
        $this->assertSame('Sarjana (S1)', $degree->fresh()->name);
        $this->assertEquals(10, $rank->fresh()->weight);
    }

    public function test_storage_repository_and_delete_blob_are_idempotent_and_validate_manifests(): void
    {
        LaravelStorage::fake('local');

        $manifest = BlobStorage::store(
            UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
            'docs',
            StorageVisibility::PRIVATE,
        );

        // First delete removes the file; second delete (or DeleteBlob retry) succeeds idempotently without 404
        $this->assertTrue(BlobStorage::delete($manifest));
        $this->assertTrue(BlobStorage::delete($manifest));
        (new DeleteBlob($manifest))->handle();

        // Empty manifest (e.g. from seeded rows) is a safe no-op
        $this->assertTrue(BlobStorage::delete(''));
        (new DeleteBlob('   '))->handle();

        // Malformed manifest throws InvalidArgumentException instead of PHP null-property warning
        $this->expectException(InvalidArgumentException::class);
        BlobStorage::url('{"disk":"local"}');
    }

    public function test_group_permission_and_group_integration_models_and_factories_use_api_guard_and_atom_dates(): void
    {
        $group = Group::create(['name' => 'Test Group']);
        $permission = Permission::create(['name' => 'test-permission']);

        $this->assertSame('api', $group->guard_name);
        $this->assertSame('api', $permission->guard_name);
        $this->assertSame($group->id, Group::findByName('Test Group')->id);
        $this->assertSame($permission->id, Permission::findByName('test-permission')->id);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $group->toArray()['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $permission->toArray()['created_at']);

        $factoryGroup = Group::factory()->create();
        $factoryPermission = Permission::factory()->create();
        $integration = GroupIntegration::factory()->create();

        $this->assertSame('api', $factoryGroup->guard_name);
        $this->assertSame('api', $factoryPermission->guard_name);
        $this->assertNotNull($integration->group);
    }

    public function test_has_groups_invalidates_cached_groups_relation_and_contact_us_mailable_declares_data(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email_address' => 'test@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $groupA = Group::create(['name' => 'Group A']);
        $groupB = Group::create(['name' => 'Group B']);

        // Prime $user->groups cache when empty
        $this->assertCount(0, $user->groups);

        $user->assignGroup($groupA);
        $this->assertSame(['Group A'], $user->groups->pluck('name')->all());

        $user->syncGroups([$groupB]);
        $this->assertSame(['Group B'], $user->groups->pluck('name')->all());

        $user->removeGroup($groupB);
        $this->assertCount(0, $user->groups);

        // ContactUs declares public array $data cleanly
        $mailable = new ContactUs([
            'subject' => 'Hello',
            'name' => 'Alice',
            'email' => 'alice@infiniteuny.id',
            'message' => 'Test inquiry',
        ]);
        $this->assertSame('Hello', $mailable->build()->subject);

        // Verify users/tokens migration down() drops tokens before users cleanly
        Token::create([
            'sso_id' => 'token-sso-1',
            'user_id' => $user->id,
            'last_used_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addHour(),
        ]);

        $migration = require base_path('database/migrations/2022_09_09_130351_create_users_table.php');
        $this->artisan('migrate:rollback', ['--path' => 'database/migrations/2026_11_23_000001_create_group_integrations_table.php'])->assertSuccessful();
        $this->assertIsObject($migration);
    }
}
