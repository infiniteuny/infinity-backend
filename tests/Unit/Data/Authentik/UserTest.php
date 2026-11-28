<?php

namespace Tests\Unit\Data\Authentik;

use App\Data\Authentik\User as AuthentikUser;
use App\Models\User as UserModel;
use RuntimeException;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_deserializes_integer_pk_and_nullable_fields_and_omits_pk_in_from_model(): void
    {
        $parsed = AuthentikUser::from([
            'pk' => 42,
            'username' => 'service_bot',
            'name' => null,
            'email' => null,
            'is_active' => true,
        ]);

        $this->assertSame(42, $parsed->sso_id);
        $this->assertSame('42', $parsed->requireSsoId());
        $this->assertNull($parsed->name);
        $this->assertNull($parsed->email_address);
        $this->assertTrue($parsed->is_active);

        $model = new UserModel([
            'name' => 'Alice Putri',
            'username' => 'aliceputri',
            'email_address' => '  alice@infiniteuny.id  ',
            'is_member' => true,
        ]);
        $model->sso_id = '42';

        $dto = AuthentikUser::fromModel($model);
        $payload = $dto->toArray();

        $this->assertArrayNotHasKey('pk', $payload);
        $this->assertArrayNotHasKey('path', $payload);
        $this->assertArrayNotHasKey('type', $payload);
        $this->assertSame('Alice Putri', $payload['name']);
        $this->assertSame('aliceputri', $payload['username']);
        $this->assertSame('alice@infiniteuny.id', $payload['email']);
        $this->assertTrue($payload['is_active']);

        foreach ([
            $dto,
            AuthentikUser::from(['pk' => null, 'username' => 'u']),
            AuthentikUser::from(['pk' => '   ', 'username' => 'u']),
        ] as $missingIdDto) {
            try {
                $missingIdDto->requireSsoId();
                $this->fail('Expected requireSsoId() to throw RuntimeException');
            } catch (RuntimeException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
