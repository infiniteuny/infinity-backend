<?php

namespace Tests\Unit\Http\Requests\Achievement;

use App\Http\Requests\Achievement\UpdateAchievementRequest;
use App\Http\Requests\CommunityGroup\StoreCommunityGroupRequest;
use App\Http\Requests\CommunityGroup\UpdateCommunityGroupRequest;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Requests\Competition\UpdateCompetitionRequest;
use App\Http\Requests\CompetitionInstance\UpdateCompetitionInstanceRequest;
use App\Http\Requests\CoreTeamDivision\StoreCoreTeamDivisionRequest;
use App\Http\Requests\CoreTeamDivision\UpdateCoreTeamDivisionRequest;
use App\Http\Requests\Degree\StoreDegreeRequest;
use App\Http\Requests\Degree\UpdateDegreeRequest;
use App\Http\Requests\Faculty\StoreFacultyRequest;
use App\Http\Requests\Faculty\UpdateFacultyRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Http\Requests\Persona\UpdatePersonaRequest;
use App\Http\Requests\ProjectGallery\UpdateProjectGalleryRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Requests\Testimonial\UpdateTestimonialRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateAchievementRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_varchar_fields_reject_256_chars_while_text_fields_remain_unbounded(): void
    {
        $tooLong = str_repeat('x', 256);
        $maxValid = str_repeat('x', 255);
        $longText = str_repeat('t', 500);

        $this->assertTrue(Validator::make(['code' => $maxValid, 'name' => $maxValid], (new StoreDegreeRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['code' => $tooLong, 'name' => 'Degree'], (new StoreDegreeRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['code' => 'S1', 'name' => $tooLong], (new StoreDegreeRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['code' => $tooLong], (new UpdateDegreeRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['code' => $maxValid, 'name' => $maxValid], (new StoreFacultyRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['code' => $tooLong, 'name' => 'Faculty'], (new StoreFacultyRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdateFacultyRequest)->rules())->passes());

        $this->assertFalse(Validator::make(['code' => $tooLong], (new UpdateMajorRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdateMajorRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['name' => $maxValid, 'priority' => 1], (new StoreCoreTeamDivisionRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong, 'priority' => 1], (new StoreCoreTeamDivisionRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdateCoreTeamDivisionRequest)->rules())->passes());

        $this->assertFalse(Validator::make(['name' => $tooLong], (new StoreCommunityGroupRequest)->rules())->passes());
        $this->assertTrue(Validator::make(['name' => $maxValid, 'description' => $longText], (new UpdateCommunityGroupRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdateCommunityGroupRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['name' => $maxValid, 'description' => $longText], (new UpdatePersonaRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdatePersonaRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['name' => $maxValid, 'shortname' => $maxValid], (new StoreCompetitionRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new StoreCompetitionRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => 'Comp', 'shortname' => $tooLong], (new StoreCompetitionRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['shortname' => $tooLong], (new UpdateCompetitionRequest)->rules())->passes());

        $instanceRules = (new UpdateCompetitionInstanceRequest)->rules();
        $this->assertTrue(Validator::make([
            'name' => $maxValid,
            'shortname' => $maxValid,
            'organizer' => $maxValid,
            'location' => $maxValid,
            'description' => $longText,
            'url' => 'https://example.com/'.str_repeat('u', 300),
        ], $instanceRules)->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], $instanceRules)->passes());
        $this->assertFalse(Validator::make(['shortname' => $tooLong], $instanceRules)->passes());
        $this->assertFalse(Validator::make(['organizer' => $tooLong], $instanceRules)->passes());
        $this->assertFalse(Validator::make(['location' => $tooLong], $instanceRules)->passes());

        $this->assertTrue(Validator::make(['name' => $maxValid], (new UpdateTeamRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdateTeamRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['competition_branch' => $maxValid], (new UpdateFundApplicationRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['competition_branch' => $tooLong], (new UpdateFundApplicationRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['competition_branch' => $maxValid, 'description' => $longText], (new UpdateAchievementRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['competition_branch' => $tooLong], (new UpdateAchievementRequest)->rules())->passes());

        $this->assertTrue(Validator::make(['name' => $maxValid, 'content' => $longText], (new UpdateTestimonialRequest)->rules())->passes());
        $this->assertFalse(Validator::make(['name' => $tooLong], (new UpdateTestimonialRequest)->rules())->passes());

        $galleryRules = (new UpdateProjectGalleryRequest)->rules();
        $this->assertTrue(Validator::make([
            'url' => 'https://example.com/'.str_repeat('a', 235),
            'description' => $longText,
        ], $galleryRules)->passes());
        $this->assertFalse(Validator::make([
            'url' => 'https://example.com/'.str_repeat('a', 236),
        ], $galleryRules)->passes());
    }
}
