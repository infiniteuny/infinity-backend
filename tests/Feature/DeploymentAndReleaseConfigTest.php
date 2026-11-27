<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentAndReleaseConfigTest extends TestCase
{
    public function test_dockerignore_excludes_local_caches_sessions_views_and_sqlite_files_while_keeping_scribe_assets(): void
    {
        $dockerignore = (string) file_get_contents(base_path('.dockerignore'));

        $this->assertStringContainsString('/bootstrap/cache/*', $dockerignore);
        $this->assertStringContainsString('!/bootstrap/cache/.gitignore', $dockerignore);
        $this->assertStringContainsString('/database/*.sqlite*', $dockerignore);
        $this->assertStringContainsString('/storage/framework/cache/*', $dockerignore);
        $this->assertStringContainsString('/storage/framework/sessions/*', $dockerignore);
        $this->assertStringContainsString('/storage/framework/views/*', $dockerignore);
        $this->assertStringNotContainsString("\n/storage/app/scribe", $dockerignore);
    }

    public function test_php_ini_allows_upload_parsing_time_and_disables_duplicate_php_zlib_compression(): void
    {
        $ini = parse_ini_file(base_path('deployment/php.ini'), true, INI_SCANNER_RAW);

        $this->assertIsArray($ini);
        $this->assertSame('60', $ini['PHP']['max_input_time'] ?? null);
        $this->assertSame('Off', $ini['zlib']['zlib.output_compression'] ?? null);
    }

    public function test_supervisord_conf_sets_explicit_ipv4_serverurl_matching_inet_http_server(): void
    {
        $supervisord = (string) file_get_contents(base_path('deployment/supervisord.conf'));

        $this->assertMatchesRegularExpression(
            '/\[supervisorctl\]\s+serverurl\s*=\s*http:\/\/127\.0\.0\.1:9001/',
            $supervisord,
        );
        $this->assertMatchesRegularExpression(
            '/\[inet_http_server\]\s+port\s*=\s*127\.0\.0\.1:9001/',
            $supervisord,
        );
    }

    public function test_release_workflow_pins_build_and_release_tag_to_immutable_commit_sha(): void
    {
        $workflow = (string) file_get_contents(base_path('.github/workflows/release.yml'));

        $this->assertStringContainsString(
            'target_commitish: ${{ needs.prepare.outputs.commit_hash || github.sha }}',
            $workflow,
        );
        $this->assertStringNotContainsString('target_commitish: ${{ github.ref_name }}', $workflow);
        $this->assertStringNotContainsString('ref: ${{ needs.prepare.outputs.commit_hash || github.ref_name }}', $workflow);
        $this->assertStringContainsString('resources/views/scribe/', $workflow);
    }
}
