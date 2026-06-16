# INFINITE Dashboard API - k6 Breakpoint Test Runner
#
# Usage:
#   .\run.ps1 -Token <BEARER_TOKEN> [-BaseUrl <URL>] [-Mode <smoke|full>]
#
# Examples:
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..."
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..." -BaseUrl "https://staging-api.example.com/infinity"
#   .\run.ps1 -Token "eyJ0eXAiOiJKV1Q..." -Mode smoke

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$Token,

    [Parameter(Mandatory = $false)]
    [string]$BaseUrl = 'https://api.infiniteuny.id/infinity',

    [Parameter(Mandatory = $false)]
    [ValidateSet('smoke', 'full')]
    [string]$Mode = 'full'
)

$ErrorActionPreference = 'Stop'

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$FixturesDir = Join-Path $ScriptDir 'fixtures'
$LogDir = Join-Path $ScriptDir 'logs'
$TimeStamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$DetailLog = Join-Path $LogDir "detail-$TimeStamp.json"

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

$env:K6_BEARER_TOKEN = $Token
$env:K6_BASE_URL = $BaseUrl
$env:K6_LOGS_DIR = $LogDir

$TestScript = Join-Path $ScriptDir 'breakpoint-test.js'

Write-Host ""
Write-Host "========================================" -ForegroundColor White
Write-Host "  INFINITE Dashboard API - k6 Breakpoint Test" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor White
Write-Host "  Mode:            $Mode" -ForegroundColor Green
Write-Host "  Base URL:        $BaseUrl" -ForegroundColor Green
Write-Host "  Log dir:         $LogDir" -ForegroundColor Green
Write-Host "  Detail log:      $DetailLog" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor White
Write-Host ""

switch ($Mode) {
    'smoke' {
        Write-Host "=== Running SMOKE test (1 VU, 1 iteration) ===" -ForegroundColor Green
        k6 run --iterations 1 --vus 1 --out "json=$DetailLog" $TestScript
    }
    'full' {
        Write-Host "=== Running FULL breakpoint test ===" -ForegroundColor Green
        k6 run --out "json=$DetailLog" $TestScript
    }
}

Write-Host ""
Write-Host "Detail log saved to: $DetailLog" -ForegroundColor Cyan
Write-Host "Summary saved to:    $LogDir" -ForegroundColor Cyan