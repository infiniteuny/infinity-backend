<?php

namespace Tests\Feature;

use Tests\TestCase;

class DatabaseBroadcastingAndEnvConfigTest extends TestCase
{
    public function test_database_config_omits_null_read_write_hosts_when_unset_and_populates_when_set(): void
    {
        $prevRead = getenv('DB_READ_HOST');
        $prevWrite = getenv('DB_WRITE_HOST');

        try {
            putenv('DB_READ_HOST');
            putenv('DB_WRITE_HOST');
            unset($_ENV['DB_READ_HOST'], $_ENV['DB_WRITE_HOST'], $_SERVER['DB_READ_HOST'], $_SERVER['DB_WRITE_HOST']);

            $configWithoutReplicas = require base_path('config/database.php');
            $this->assertArrayNotHasKey('read', $configWithoutReplicas['connections']['pgsql']);
            $this->assertArrayNotHasKey('write', $configWithoutReplicas['connections']['pgsql']);
            $this->assertSame('127.0.0.1', $configWithoutReplicas['connections']['pgsql']['host']);

            putenv('DB_READ_HOST=replica-1.internal, replica-2.internal');
            putenv('DB_WRITE_HOST=primary.internal');
            $_ENV['DB_READ_HOST'] = 'replica-1.internal, replica-2.internal';
            $_ENV['DB_WRITE_HOST'] = 'primary.internal';

            $configWithReplicas = require base_path('config/database.php');
            $this->assertSame(
                ['replica-1.internal', 'replica-2.internal'],
                $configWithReplicas['connections']['pgsql']['read']['host'],
            );
            $this->assertSame(
                ['primary.internal'],
                $configWithReplicas['connections']['pgsql']['write']['host'],
            );
            $this->assertTrue($configWithReplicas['connections']['pgsql']['sticky']);
        } finally {
            if ($prevRead === false) {
                putenv('DB_READ_HOST');
                unset($_ENV['DB_READ_HOST']);
            } else {
                putenv("DB_READ_HOST={$prevRead}");
                $_ENV['DB_READ_HOST'] = $prevRead;
            }

            if ($prevWrite === false) {
                putenv('DB_WRITE_HOST');
                unset($_ENV['DB_WRITE_HOST']);
            } else {
                putenv("DB_WRITE_HOST={$prevWrite}");
                $_ENV['DB_WRITE_HOST'] = $prevWrite;
            }
        }
    }

    public function test_broadcasting_defaults_to_null_driver_and_supervisord_omits_invalid_program_minfds(): void
    {
        $prevBroadcast = getenv('BROADCAST_CONNECTION');

        try {
            putenv('BROADCAST_CONNECTION');
            unset($_ENV['BROADCAST_CONNECTION'], $_SERVER['BROADCAST_CONNECTION']);

            $broadcasting = require base_path('config/broadcasting.php');
            $this->assertSame('null', $broadcasting['default']);
        } finally {
            if ($prevBroadcast === false) {
                putenv('BROADCAST_CONNECTION');
                unset($_ENV['BROADCAST_CONNECTION']);
            } else {
                putenv("BROADCAST_CONNECTION={$prevBroadcast}");
                $_ENV['BROADCAST_CONNECTION'] = $prevBroadcast;
            }
        }

        $reverbConf = (string) file_get_contents(base_path('deployment/supervisord.reverb.conf'));
        $servicesConf = (string) file_get_contents(base_path('deployment/supervisord.services.conf'));

        $this->assertStringNotContainsString('minfds', $reverbConf);
        $this->assertStringNotContainsString('minfds', $servicesConf);
    }

    public function test_env_example_includes_oidc_app_path_and_authentik_internal_group_key(): void
    {
        $envExample = (string) file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString(
            'OIDC_CONFIGURATIONS_URI="https://auth.infiniteuny.id/application/o/infinity/.well-known/openid-configuration"',
            $envExample,
        );
        $this->assertStringContainsString('AUTHENTIK_INTERNAL_GROUP_ID=', $envExample);
        $this->assertStringNotContainsString('OUTLINE_CG_ADMIN_GROUP_ID', $envExample);
        $this->assertStringNotContainsString('OUTLINE_XCG_ADMIN_GROUP_ID', $envExample);
    }
}
