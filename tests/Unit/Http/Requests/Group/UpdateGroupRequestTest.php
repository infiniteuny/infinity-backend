<?php

namespace Tests\Unit\Http\Requests\Group;

use App\Http\Requests\Group\UpdateGroupRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateGroupRequestTest extends TestCase
{
    public function test_rejects_reserved_managed_and_cohort_group_names_on_rename(): void
    {
        $validate = static function (array $payload): bool {
            $request = UpdateGroupRequest::create('/v1/groups/id', 'PATCH', $payload);

            return Validator::make($request->all(), $request->rules())->passes();
        };

        $this->assertTrue($validate([]));
        $this->assertTrue($validate(['name' => 'Research Guild']));

        foreach ([
            'Core Team',
            'Ex Core Team',
            'CG Admin',
            'Ex CG Admin',
            'Active Member',
            'Inactive Member',
            'Member',
            'Administrator',
            'Core Team 2030',
            'CG Admin 2030',
        ] as $reserved) {
            $this->assertFalse($validate(['name' => $reserved]), "Expected '{$reserved}' to be rejected.");
        }
    }
}
