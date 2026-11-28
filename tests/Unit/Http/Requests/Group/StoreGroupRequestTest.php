<?php

namespace Tests\Unit\Http\Requests\Group;

use App\Http\Requests\Group\StoreGroupRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreGroupRequestTest extends TestCase
{
    public function test_rejects_reserved_managed_and_cohort_group_names_and_allows_custom_names(): void
    {
        $validate = static function (string $name): bool {
            $request = StoreGroupRequest::create('/v1/groups', 'POST', [
                'name' => $name,
                'guard_name' => 'api',
            ]);

            return Validator::make($request->all(), $request->rules())->passes();
        };

        foreach ([
            'Core Team',
            'core team',
            'Ex Core Team',
            'CG Admin',
            'Ex CG Admin',
            'Active Member',
            'Inactive Member',
            'Member',
            'Administrator',
            'ADMINISTRATOR',
            'Core Team 2030',
            'core team 2030',
            'Core Team   2030',
            'CG Admin 2030',
            'cg admin 2030',
            str_repeat('a', 256),
        ] as $reservedOrInvalid) {
            $this->assertFalse($validate($reservedOrInvalid), "Expected '{$reservedOrInvalid}' to be rejected.");
        }

        $this->assertTrue($validate('Design Guild'));
        $this->assertTrue($validate('Event Committee 2030'));
        $this->assertTrue($validate(str_repeat('a', 255)));
    }
}
