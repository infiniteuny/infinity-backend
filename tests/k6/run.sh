#!/usr/bin/env sh
# INFINITE Dashboard API - k6 Breakpoint Test Runner
#
# Usage:
#   ./run.sh <BEARER_TOKEN> [BASE_URL] [smoke|full]
#
# Examples:
#   ./run.sh "eyJ0eXAiOiJKV1Q..."
#   ./run.sh "eyJ0eXAiOiJKV1Q..." "https://staging-api.example.com/infinity" "smoke"

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TOKEN="${1:?Usage: $0 <BEARER_TOKEN> [BASE_URL] [smoke|full]}"
BASE_URL="${2:-https://api.infiniteuny.id/infinity}"
MODE="${3:-full}"

LOG_DIR="$SCRIPT_DIR/logs"
mkdir -p "$LOG_DIR"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DETAIL_LOG="$LOG_DIR/detail-$TIMESTAMP.json"

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

export K6_BEARER_TOKEN="$TOKEN"
export K6_BASE_URL="$BASE_URL"
export K6_LOGS_DIR="$LOG_DIR"

TEST_SCRIPT="$SCRIPT_DIR/breakpoint-test.js"

echo ""
echo "========================================"
echo "  INFINITE Dashboard API - k6 Breakpoint Test"
echo "========================================"
echo "  Mode:            $MODE"
echo "  Base URL:        $BASE_URL"
echo "  Log dir:         $LOG_DIR"
echo "  Detail log:      $DETAIL_LOG"
echo "========================================"
echo ""

if [ "$MODE" = "smoke" ]; then
    echo "=== Running SMOKE test (1 VU, 1 iteration) ==="
    k6 run --iterations 1 --vus 1 --out "json=$DETAIL_LOG" "$TEST_SCRIPT"
else
    echo "=== Running FULL breakpoint test ==="
    k6 run --out "json=$DETAIL_LOG" "$TEST_SCRIPT"
fi

echo ""
echo "Detail log saved to: $DETAIL_LOG"
echo "Summary saved to:    $LOG_DIR"