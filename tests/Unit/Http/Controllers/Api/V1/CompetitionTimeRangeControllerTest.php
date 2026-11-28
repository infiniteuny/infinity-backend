<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\CompetitionTimeRangeController;
use ReflectionMethod;
use Tests\TestCase;

class CompetitionTimeRangeControllerTest extends TestCase
{
    public function test_update_docblock_declares_api_resource_and_model(): void
    {
        $updateDoc = (string) (new ReflectionMethod(CompetitionTimeRangeController::class, 'update'))->getDocComment();

        $this->assertStringContainsString('@apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource', $updateDoc);
        $this->assertStringContainsString('@apiResourceModel App\Models\CompetitionTimeRange', $updateDoc);
    }
}
