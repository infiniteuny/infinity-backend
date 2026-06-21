#!/usr/bin/env sh
# INFINITE Dashboard API - k6 Load Test Runner
#
# Usage:
#   ./run.sh <BEARER_TOKEN> [BASE_URL] [smoke|breakpoint] [--cloud]
#
# Modes:
#   smoke  - Runs smoke-test.js (11 scenarios, 1 VU each, 30s duration)
#            Verifies all endpoints work correctly with minimal load
#   breakpoint   - Runs breakpoint-test.js (11 scenarios, multi-VU, 23min duration)
#            Breakpoint load test with 80/15/5 traffic distribution
#
# Examples:
#   ./run.sh "eyJ0eXAiOiJKV1Q..."                    # Breakpoint test (default)
#   ./run.sh "eyJ0eXAiOiJKV1Q..." "" smoke           # Smoke test
#   ./run.sh "eyJ0eXAiOiJKV1Q..." "" breakpoint --cloud    # Breakpoint test on cloud

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TOKEN="${1:?Usage: $0 <BEARER_TOKEN> [BASE_URL] [smoke|breakpoint] [--cloud]}"
BASE_URL="${2:-https://api.infiniteuny.id/infinity}"
MODE="${3:-breakpoint}"
USE_CLOUD=0

for arg in "$@"; do
    case "$arg" in
        --cloud) USE_CLOUD=1 ;;
    esac
done

LOG_DIR="$SCRIPT_DIR/logs"
mkdir -p "$LOG_DIR"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DETAIL_LOG="$LOG_DIR/detail-$TIMESTAMP.json"
SUMMARY_LOG="$LOG_DIR/summary-$TIMESTAMP.json"

FIXTURES_DIR="$SCRIPT_DIR/fixtures"
MISSING=0
for f in test-image.jpg test-animation.gif test-document.pdf; do
    if [ ! -f "$FIXTURES_DIR/$f" ]; then
        echo "WARNING: Missing fixture: $FIXTURES_DIR/$f"
        MISSING=1
    fi
done

if [ "$MISSING" = "1" ]; then
    echo ""
    echo "Place the following files in $FIXTURES_DIR/:"
    echo "  - test-image.jpg       (for image, photo, logo fields)"
    echo "  - test-animation.gif   (for animation fields)"
    echo "  - test-document.pdf    (for letter_of_acceptance, proposal fields)"
    echo ""
    read -p "Continue without fixtures? (y/N) " -r REPLY
    if [ "$REPLY" != "y" ] && [ "$REPLY" != "Y" ]; then
        exit 1
    fi
fi

if [ "$MODE" = "smoke" ]; then
    TEST_SCRIPT="$SCRIPT_DIR/smoke-test.js"
else
    TEST_SCRIPT="$SCRIPT_DIR/breakpoint-test.js"
fi

HTML_REPORT="$LOG_DIR/html-report-$TIMESTAMP.html"

ENV_FLAGS="--env K6_BEARER_TOKEN=$TOKEN --env K6_BASE_URL=$BASE_URL --env K6_LOGS_DIR=$LOG_DIR --env K6_WEB_DASHBOARD=true --env K6_WEB_DASHBOARD_EXPORT=$HTML_REPORT"

CLOUD_EXEC_MODE=""
if [ "$USE_CLOUD" = "1" ]; then
    echo "Checking k6 cloud login status..."
    LOGIN_OUTPUT=$(k6 cloud login -s 2>&1)
    if echo "$LOGIN_OUTPUT" | grep -q "token:.*<not set>"; then
        echo ""
        echo "ERROR: Not logged in to Grafana Cloud."
        echo "Please run: k6 cloud login"
        echo ""
        exit 1
    fi

    echo "Logged in to Grafana Cloud."
    echo ""
    echo "Select execution mode:"
    echo "  [1] Cloud (run on Grafana Cloud infrastructure)"
    echo "  [2] Local with cloud logging (run locally, send results to cloud)"
    echo ""
    printf "Enter choice (1 or 2, default: 1): "
    read -r CLOUD_CHOICE

    if [ "$CLOUD_CHOICE" = "2" ]; then
        CLOUD_EXEC_MODE="local"
        echo "Using: Local execution with cloud logging"
    else
        CLOUD_EXEC_MODE="cloud"
        echo "Using: Cloud execution"
    fi
fi

echo ""
echo "========================================"
echo "  INFINITE Dashboard API - k6 Load Test"
echo "========================================"
echo "  Mode:            $MODE"
echo "  Test script:     $(basename "$TEST_SCRIPT")"
echo "  Base URL:        $BASE_URL"
echo "  Log dir:         $LOG_DIR"
echo "  Detail log:      $DETAIL_LOG"
echo "  Summary log:     $SUMMARY_LOG"
if [ "$USE_CLOUD" = "1" ]; then
    echo "  Cloud mode:      $CLOUD_EXEC_MODE"
fi
echo "========================================"
echo ""

if [ "$MODE" = "smoke" ]; then
    if [ "$USE_CLOUD" = "1" ]; then
        if [ "$CLOUD_EXEC_MODE" = "local" ]; then
            echo "=== Running SMOKE test (11 scenarios, 1 VU each, 30s) - Local with cloud logging ==="
            k6 cloud run --local-execution --summary-export "$SUMMARY_LOG" $ENV_FLAGS "$TEST_SCRIPT"
        else
            echo "=== Running SMOKE test (11 scenarios, 1 VU each, 30s) - Cloud ==="
            k6 cloud run --summary-export "$SUMMARY_LOG" $ENV_FLAGS "$TEST_SCRIPT"
        fi
    else
        echo "=== Running SMOKE test (11 scenarios, 1 VU each, 30s) ==="
        k6 run --out "json=$DETAIL_LOG" $ENV_FLAGS "$TEST_SCRIPT"
    fi
else
    if [ "$USE_CLOUD" = "1" ]; then
        if [ "$CLOUD_EXEC_MODE" = "local" ]; then
            echo "=== Running BREAKPOINT test - Local with cloud logging ==="
            k6 cloud run --local-execution --summary-export "$SUMMARY_LOG" $ENV_FLAGS "$TEST_SCRIPT"
        else
            echo "=== Running BREAKPOINT test - Cloud ==="
            k6 cloud run --summary-export "$SUMMARY_LOG" $ENV_FLAGS "$TEST_SCRIPT"
        fi
    else
        echo "=== Running BREAKPOINT test ==="
        k6 run --out "json=$DETAIL_LOG" $ENV_FLAGS "$TEST_SCRIPT"
    fi
fi

echo ""
if [ "$USE_CLOUD" = "1" ]; then
    echo "Summary log saved to: $SUMMARY_LOG"
else
    echo "Detail log saved to:  $DETAIL_LOG"
fi
echo "Log directory:        $LOG_DIR"