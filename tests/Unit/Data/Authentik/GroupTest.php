<?php

namespace Tests\Unit\Data\Authentik;

use App\Data\Authentik\Group as AuthentikGroup;
use App\Models\Group as GroupModel;
use App\Models\User as UserModel;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class GroupTest extends TestCase
{
    public function test_from_model_filters_unsynced_or_non_numeric_user_sso_ids_and_omits_optional_fields(): void
    {
        $u1 = new UserModel;
        $u1->sso_id = '101';

        $u2 = new UserModel;
        $u2->sso_id = null;

        $u3 = new UserModel;
        $u3->sso_id = 'non-numeric-uuid';

        $u4 = new UserModel;
        $u4->sso_id = ' 101 ';

        $u5 = new UserModel;
        $u5->sso_id = '202';

        $group = new GroupModel(['name' => 'Core Team 2026']);
        $group->setRelation('users', new Collection([$u1, $u2, $u3, $u4, $u5]));

        $payload = AuthentikGroup::fromModel($group)->toArray();

        $this->assertArrayNotHasKey('pk', $payload);
        $this->assertArrayNotHasKey('parents', $payload);
        $this->assertArrayNotHasKey('is_superuser', $payload);
        $this->assertSame('Core Team 2026', $payload['name']);
        $this->assertSame([101, 202], $payload['users']);
    }
}
