<?php

namespace Tests\Feature;

use App\Http\Requests\Config\UpdateConfigRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Jobs\CreateDocsGroup;
use App\Jobs\CreateSsoGroup;
use App\Jobs\CreateSsoUser;
use App\Jobs\DeleteBlob;
use App\Jobs\UpdateDocsGroup;
use App\Jobs\UpdateSsoGroup;
use App\Jobs\UpdateSsoUser;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\Config;
use App\Models\CoreTeam;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestBooleanAndValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([
            CreateSsoUser::class,
            UpdateSsoUser::class,
            CreateSsoGroup::class,
            UpdateSsoGroup::class,
            CreateDocsGroup::class,
            UpdateDocsGroup::class,
            DeleteBlob::class,
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        foreach ([
            'create-config',
            'update-config',
            'create-community-group',
            'update-community-group',
            'create-core-team',
            'update-core-team',
            'create-community-group-admin',
            'update-community-group-admin',
            'create-user',
            'update-user',
            'manage-user-membership',
        ] as $permission) {
            $this->admin->givePermissionTo(Permission::create([
                'name' => $permission,
                'guard_name' => 'api',
            ]));
        }
    }

    public function test_null_empty_and_invalid_boolean_inputs_fail_validation_instead_of_coercing_to_false(): void
    {
        $config = Config::create([
            'key' => 'allow_reregistration',
            'value' => 'true',
            'type' => 'BOOLEAN',
            'is_private' => true,
        ]);

        foreach ([null, '', '   ', 'maybe', 2, []] as $invalidBoolean) {
            $this->actingAs($this->admin, 'api_headers')
                ->patchJson("/v1/configs/{$config->key}", [
                    'is_private' => $invalidBoolean,
                ])
                ->assertStatus(422)
                ->assertJsonPath('status', 'fail')
                ->assertJsonStructure(['data' => ['details' => ['is_private']]]);

            $this->assertTrue($config->fresh()->is_private);
        }

        // Bypassing ConvertEmptyStringsToNull directly on the FormRequest normalizes "" to null (not false) and fails boolean
        $rawRequest = UpdateConfigRequest::create('/v1/configs/allow_reregistration', 'PATCH', [
            'is_private' => '',
        ]);
        $rawMethod = new \ReflectionMethod($rawRequest, 'prepareForValidation');
        $rawMethod->invoke($rawRequest);
        $this->assertNull($rawRequest->input('is_private'));
        $this->assertTrue(Validator::make($rawRequest->all(), $rawRequest->rules())->fails());

        // Invalid string on a required boolean fails the boolean rule (not required)
        $invalidStoreResponse = $this->actingAs($this->admin, 'api_headers')
            ->postJson('/v1/configs', [
                'key' => 'new_flag',
                'value' => '1',
                'type' => 'BOOLEAN',
                'is_private' => 'not-a-bool',
            ])
            ->assertStatus(422);

        $this->assertStringContainsString(
            'true or false',
            (string) $invalidStoreResponse->json('data.details.is_private.0'),
        );
    }

    public function test_valid_boolean_strings_and_zero_one_integers_coerce_cleanly_across_requests(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $this->actingAs($this->admin, 'api_headers')
            ->postJson('/v1/configs', [
                'key' => 'string_true_config',
                'value' => 'enabled',
                'type' => 'STRING',
                'is_private' => 'true',
            ])
            ->assertCreated()
            ->assertJsonPath('data.config.is_private', true);

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson('/v1/configs/string_true_config', [
                'is_private' => '0',
            ])
            ->assertOk()
            ->assertJsonPath('data.config.is_private', false);

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson('/v1/configs/string_true_config', [
                'is_private' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.config.is_private', true);

        // Multipart upload with string boolean 'true' / 'false'
        $groupResponse = $this->actingAs($this->admin, 'api_headers')
            ->post('/v1/community-groups', [
                'name' => 'Web Engineering',
                'priority' => 1,
                'description' => 'Frontend and backend',
                'logo' => UploadedFile::fake()->image('logo.png'),
                'is_active' => 'true',
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $communityGroupId = $groupResponse->json('data.community_group.id');
        $this->assertTrue((bool) CommunityGroup::findOrFail($communityGroupId)->is_active);

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson("/v1/community-groups/{$communityGroupId}", [
                'is_active' => null,
            ])
            ->assertStatus(422);

        $this->assertTrue((bool) CommunityGroup::findOrFail($communityGroupId)->is_active);

        // BlobRequest query string boolean coercion and null/invalid rejection
        Storage::disk('local')->put('docs/sample.txt', 'hello world');
        $signedRelativeUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(5), [
            'blob' => 'docs/sample.txt',
        ], absolute: false);

        $inlineResponse = $this->get($signedRelativeUrl.'&force_download=false')
            ->assertOk();
        $this->assertStringContainsString('inline', (string) $inlineResponse->headers->get('Content-Disposition'));

        $downloadResponse = $this->get($signedRelativeUrl.'&force_download=true')
            ->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadResponse->headers->get('Content-Disposition'));

        $this->getJson($signedRelativeUrl.'&force_download=maybe')
            ->assertStatus(422);

        $this->getJson($signedRelativeUrl.'&force_download=')
            ->assertStatus(422);
    }

    public function test_store_and_update_user_request_validated_supports_scalar_keys_and_strips_restricted_fields(): void
    {
        $limitedEditor = User::create([
            'name' => 'Limited Editor',
            'username' => 'limitededitor',
            'email_address' => 'editor@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $limitedEditor->givePermissionTo(['create-user', 'update-user']);

        $storePayload = [
            'name' => 'New Member',
            'username' => 'newmember',
            'email_address' => 'newmember@infiniteuny.id',
            'phone_number' => '+6281111111103',
            'student_id' => '24520241003',
            'major_id' => $this->major->id,
            'links' => ['github' => 'https://github.com/infiniteuny'],
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_member' => true,
            'is_extraordinary' => true,
        ];

        $storeRequest = StoreUserRequest::create('/v1/users', 'POST', $storePayload);
        $storeRequest->setContainer($this->app)->setRedirector($this->app['redirect']);
        $storeRequest->setUserResolver(fn () => $limitedEditor);
        $storeRequest->validateResolved();

        $this->assertSame('New Member', $storeRequest->validated('name'));
        $this->assertSame('https://github.com/infiniteuny', $storeRequest->validated('links.github'));
        $this->assertNull($storeRequest->validated('is_member'));
        $this->assertSame('fallback', $storeRequest->validated('is_member', 'fallback'));
        $this->assertArrayNotHasKey('is_member', $storeRequest->validated());
        $this->assertArrayNotHasKey('is_extraordinary', $storeRequest->validated());
        $this->assertArrayNotHasKey('start_date', $storeRequest->validated());
        $this->assertArrayNotHasKey('end_date', $storeRequest->validated());

        // Even if a custom validator returned restricted keys, validated($key) still strips them
        $updateRequest = UpdateUserRequest::create("/v1/users/{$limitedEditor->id}", 'PATCH', [
            'name' => 'Updated Name',
            'is_member' => true,
        ]);
        $updateRequest->setContainer($this->app)->setRedirector($this->app['redirect']);
        $updateRequest->setUserResolver(fn () => $limitedEditor);
        $updateRequest->setValidator(Validator::make(
            ['name' => 'Updated Name', 'is_member' => true, 'is_extraordinary' => true],
            ['name' => ['required', 'string'], 'is_member' => ['boolean'], 'is_extraordinary' => ['boolean']],
        ));

        $this->assertSame('Updated Name', $updateRequest->validated('name'));
        $this->assertNull($updateRequest->validated('is_member'));
        $this->assertFalse($updateRequest->validated('is_extraordinary', false));

        // Admin WITH manage-user-membership receives scalar boolean from validated('is_member')
        $adminUpdateRequest = UpdateUserRequest::create("/v1/users/{$limitedEditor->id}", 'PATCH', [
            'is_member' => 'true',
        ]);
        $adminUpdateRequest->setContainer($this->app)->setRedirector($this->app['redirect']);
        $adminUpdateRequest->setUserResolver(fn () => $this->admin);
        $adminUpdateRequest->validateResolved();

        $this->assertTrue($adminUpdateRequest->validated('is_member'));
    }

    public function test_core_team_and_community_group_admin_year_enforces_1901_to_2155_bounds_and_bails_before_unique_query(): void
    {
        Group::create(['name' => 'Core Team', 'guard_name' => 'api', 'is_managed' => true]);
        Group::create(['name' => 'Ex Core Team', 'guard_name' => 'api', 'is_managed' => true]);
        Group::create(['name' => 'Community Group Admin', 'guard_name' => 'api', 'is_managed' => true]);
        Group::create(['name' => 'Ex Community Group Admin', 'guard_name' => 'api', 'is_managed' => true]);

        foreach ([1900, 2156, 999999999999, -2025, 'not-a-year'] as $invalidYear) {
            DB::enableQueryLog();
            DB::flushQueryLog();

            $this->actingAs($this->admin, 'api_headers')
                ->postJson('/v1/core-teams', [
                    'year' => $invalidYear,
                    'is_active' => false,
                ])
                ->assertStatus(422)
                ->assertJsonStructure(['data' => ['details' => ['year']]]);

            $this->actingAs($this->admin, 'api_headers')
                ->postJson('/v1/community-group-admins', [
                    'year' => $invalidYear,
                    'is_active' => false,
                ])
                ->assertStatus(422)
                ->assertJsonStructure(['data' => ['details' => ['year']]]);

            $uniqueQueries = array_filter(
                DB::getQueryLog(),
                static fn (array $entry): bool => str_contains($entry['query'], 'core_teams')
                    || str_contains($entry['query'], 'community_group_admins'),
            );
            $this->assertCount(0, $uniqueQueries, "Expected bail to skip unique DB query for invalid year: {$invalidYear}");
            DB::disableQueryLog();
        }

        // Valid boundary and standard cohort years succeed on both store and update
        $coreTeamResponse = $this->actingAs($this->admin, 'api_headers')
            ->postJson('/v1/core-teams', [
                'year' => 1901,
                'is_active' => 'false',
            ])
            ->assertCreated();

        $coreTeamId = $coreTeamResponse->json('data.core_team.id');

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson("/v1/core-teams/{$coreTeamId}", [
                'year' => 2156,
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['details' => ['year']]]);

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson("/v1/core-teams/{$coreTeamId}", [
                'year' => 2155,
            ])
            ->assertOk();

        $this->assertSame(2155, (int) CoreTeam::findOrFail($coreTeamId)->year);

        $cgAdminResponse = $this->actingAs($this->admin, 'api_headers')
            ->postJson('/v1/community-group-admins', [
                'year' => 2026,
                'is_active' => 'false',
            ])
            ->assertCreated();

        $cgAdminId = $cgAdminResponse->json('data.community_group_admin.id');

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson("/v1/community-group-admins/{$cgAdminId}", [
                'year' => 1800,
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['details' => ['year']]]);

        $this->actingAs($this->admin, 'api_headers')
            ->patchJson("/v1/community-group-admins/{$cgAdminId}", [
                'year' => 2027,
            ])
            ->assertOk();

        $this->assertSame(2027, (int) CommunityGroupAdmin::findOrFail($cgAdminId)->year);
    }
}
