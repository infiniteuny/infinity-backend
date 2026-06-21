# INFINITE Dashboard API - k6 Load Test Runner
#
# Usage:
#   .\run.ps1 -Token <BEARER_TOKEN> [-BaseUrl <URL>] [-Mode <smoke|breakpoint>] [-UseCloud]
#
# Modes:
#   smoke      - Runs smoke-test.js (11 scenarios, 1 VU each, 30s duration)
#                Verifies all endpoints work correctly with minimal load
#   breakpoint - Runs breakpoint-test.js (11 scenarios, multi-VU, 23min duration)
#                Breakpoint load test with 80/15/5 traffic distribution
#
# Examples:
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..."
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..." -BaseUrl "https://staging-api.example.com/infinity"
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..." -Mode smoke
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..." -Mode breakpoint -UseCloud

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$Token,

    [Parameter(Mandatory = $false)]
    [string]$BaseUrl = 'https://api.infiniteuny.id/infinity',

    [Parameter(Mandatory = $false)]
    [ValidateSet('smoke', 'breakpoint')]
    [string]$Mode = 'breakpoint',

    [Parameter(Mandatory = $false)]
    [switch]$UseCloud
)

$ErrorActionPreference = 'Stop'

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$FixturesDir = Join-Path $ScriptDir 'fixtures'
$LogDir = Join-Path $ScriptDir 'logs'
$TimeStamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$DetailLog = Join-Path $LogDir "detail-$TimeStamp.json"
$SummaryLog = Join-Path $LogDir "summary-$TimeStamp.json"

if (-not (Test-Path -LiteralPath $LogDir)) {
    New-Item -ItemType Directory -Path $LogDir -Force | Out-Null
}

# Check for fixture files
$MissingFiles = @()
$FixtureNames = @('test-image.jpg', 'test-animation.gif', 'test-document.pdf')

foreach ($f in $FixtureNames) {
    $Path = Join-Path $FixturesDir $f
    if (-not (Test-Path -LiteralPath $Path)) {
        $MissingFiles += $f
    }
}

if ($MissingFiles.Count -gt 0) {
    Write-Host ""
    Write-Host "WARNING: Missing fixture files in $FixturesDir\" -ForegroundColor Yellow
    foreach ($f in $MissingFiles) {
        Write-Host "  - $f" -ForegroundColor Yellow
    }
    Write-Host ""
    Write-Host "  - test-image.jpg       (for image, photo, logo fields)" -ForegroundColor Cyan
    Write-Host "  - test-animation.gif   (for animation fields)" -ForegroundColor Cyan
    Write-Host "  - test-document.pdf    (for letter_of_acceptance, proposal fields)" -ForegroundColor Cyan
    Write-Host ""

    $Continue = Read-Host "Continue without fixtures? (y/N)"
    if ($Continue -ne 'y' -and $Continue -ne 'Y') {
        exit 1
    }
}

if ($Mode -eq 'smoke') {
    $TestScript = Join-Path $ScriptDir 'smoke-test.js'
} else {
    $TestScript = Join-Path $ScriptDir 'breakpoint-test.js'
}

$HtmlReport = Join-Path $LogDir "html-report-$TimeStamp.html"

$EnvFlags = @(
    "--env", "K6_BEARER_TOKEN=$Token",
    "--env", "K6_BASE_URL=$BaseUrl",
    "--env", "K6_LOGS_DIR=$LogDir",
    "--env", "K6_WEB_DASHBOARD=true",
    "--env", "K6_WEB_DASHBOARD_EXPORT=$HtmlReport"
)

$CloudExecutionMode = $null
if ($UseCloud) {
    Write-Host "Checking k6 cloud login status..." -ForegroundColor Cyan
    $LoginCheckOutput = & k6 cloud login -s 2>&1 | Out-String

    if ($LoginCheckOutput -match 'token:\s*<not set>') {
        Write-Host ""
        Write-Host "ERROR: Not logged in to Grafana Cloud." -ForegroundColor Red
        Write-Host "Please run: k6 cloud login" -ForegroundColor Yellow
        Write-Host ""
        exit 1
    }

    Write-Host "Logged in to Grafana Cloud." -ForegroundColor Green
    Write-Host ""
    Write-Host "Select execution mode:" -ForegroundColor Cyan
    Write-Host "  [1] Cloud (run on Grafana Cloud infrastructure)" -ForegroundColor White
    Write-Host "  [2] Local with cloud logging (run locally, send results to cloud)" -ForegroundColor White
    Write-Host ""
    $CloudChoice = Read-Host "Enter choice (1 or 2, default: 1)"

    if ($CloudChoice -eq '2') {
        $CloudExecutionMode = 'local'
        Write-Host "Using: Local execution with cloud logging" -ForegroundColor Green
    } else {
        $CloudExecutionMode = 'cloud'
        Write-Host "Using: Cloud execution" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor White
Write-Host "  INFINITE Dashboard API - k6 Load Test" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor White
Write-Host "  Mode:            $Mode" -ForegroundColor Green
Write-Host "  Test script:     $(Split-Path -Leaf $TestScript)" -ForegroundColor Green
Write-Host "  Base URL:        $BaseUrl" -ForegroundColor Green
Write-Host "  Log dir:         $LogDir" -ForegroundColor Green
Write-Host "  Detail log:      $DetailLog" -ForegroundColor Green
Write-Host "  Summary log:     $SummaryLog" -ForegroundColor Green
if ($UseCloud) {
    Write-Host "  Cloud mode:      $CloudExecutionMode" -ForegroundColor Green
}
Write-Host "========================================" -ForegroundColor White
Write-Host ""

switch ($Mode) {
    'smoke' {
        if ($UseCloud) {
            if ($CloudExecutionMode -eq 'local') {
                Write-Host "=== Running SMOKE test (11 scenarios, 1 VU each, 30s) - Local with cloud logging ===" -ForegroundColor Green
                k6 cloud run --local-execution --summary-export $SummaryLog @EnvFlags $TestScript
            } else {
                Write-Host "=== Running SMOKE test (11 scenarios, 1 VU each, 30s) - Cloud ===" -ForegroundColor Green
                k6 cloud run --summary-export $SummaryLog @EnvFlags $TestScript
            }
        } else {
            Write-Host "=== Running SMOKE test (11 scenarios, 1 VU each, 30s) ===" -ForegroundColor Green
            k6 run --out "json=$DetailLog" @EnvFlags $TestScript
        }
    }
    'breakpoint' {
        if ($UseCloud) {
            if ($CloudExecutionMode -eq 'local') {
                Write-Host "=== Running BREAKPOINT test - Local with cloud logging ===" -ForegroundColor Green
                k6 cloud run --local-execution --summary-export $SummaryLog @EnvFlags $TestScript
            } else {
                Write-Host "=== Running BREAKPOINT test - Cloud ===" -ForegroundColor Green
                k6 cloud run --summary-export $SummaryLog @EnvFlags $TestScript
            }
        } else {
            Write-Host "=== Running BREAKPOINT test ===" -ForegroundColor Green
            k6 run --out "json=$DetailLog" @EnvFlags $TestScript
        }
    }
}

Write-Host ""
if ($UseCloud) {
    Write-Host "Summary log saved to: $SummaryLog" -ForegroundColor Cyan
} else {
    Write-Host "Detail log saved to:  $DetailLog" -ForegroundColor Cyan
}
Write-Host "Log directory:        $LogDir" -ForegroundColor Cyan
