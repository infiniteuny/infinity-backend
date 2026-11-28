<?php

namespace Tests\Unit\Utils;

use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\UserController;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use App\Utils\GetFromFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Tools\DocumentationConfig;
use Knuckles\Scribe\Tools\Globals;
use ReflectionMethod;
use Tests\TestCase;

class GetFromFormRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Globals::$__instantiateFormRequestUsing = null;
        parent::tearDown();
    }

    public function test_extracts_full_admin_and_approver_schemas_with_null_user_resolver_even_when_unprivileged_user_is_authenticated(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $unprivileged = User::create([
            'name' => 'Dummy User',
            'username' => 'dummyuser',
            'email_address' => 'dummy@infiniteuny.id',
            'phone_number' => '+6281111111999',
            'student_id' => '24520249999',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $this->actingAs($unprivileged, 'api_headers');

        Globals::$__instantiateFormRequestUsing = static function (string $className) use ($unprivileged): FormRequest {
            /** @var FormRequest $req */
            $req = new $className;
            $req->setUserResolver(static fn () => $unprivileged);

            return $req;
        };

        $strategy = new GetFromFormRequest(new DocumentationConfig([]));

        $userRoute = new Route(['POST'], 'v1/users', [UserController::class, 'store']);
        $userEndpoint = new ExtractedEndpointData([
            'route' => $userRoute,
            'method' => new ReflectionMethod(UserController::class, 'store'),
        ]);

        $userParams = $strategy->getParametersFromFormRequestWithBinding($userEndpoint, $userRoute);

        $this->assertArrayHasKey('start_date', $userParams);
        $this->assertArrayHasKey('end_date', $userParams);
        $this->assertArrayHasKey('is_member', $userParams);
        $this->assertArrayHasKey('is_extraordinary', $userParams);

        $achievementRoute = new Route(['POST'], 'v1/achievements', [AchievementController::class, 'store']);
        $achievementEndpoint = new ExtractedEndpointData([
            'route' => $achievementRoute,
            'method' => new ReflectionMethod(AchievementController::class, 'store'),
        ]);

        $achievementParams = $strategy->getParametersFromFormRequestWithBinding($achievementEndpoint, $achievementRoute);

        $this->assertArrayHasKey('status', $achievementParams);
        $this->assertEqualsCanonicalizing(
            ['PENDING', 'REJECTED', 'ACCEPTED'],
            $achievementParams['status']['enumValues'] ?? [],
        );
    }
}
