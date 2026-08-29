#!/usr/bin/env sh

set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT_DIR"

PATTERN='Deprecated|Warning|Notice|Fatal|ErrorException'
FAIL=0

if ! command -v php >/dev/null 2>&1; then
    echo "[ERROR] php binary is not available"
    exit 1
fi

if ! command -v curl >/dev/null 2>&1; then
    echo "[ERROR] curl binary is not available"
    exit 1
fi

echo "[1/4] PHP version"
php -v | sed -n '1,3p'

echo "[2/4] Core lint"
if ! find core -name '*.php' -print0 | xargs -0 -n1 php -l >/tmp/divak-php85-lint.log 2>&1; then
    cat /tmp/divak-php85-lint.log
    exit 1
fi

echo "[3/4] CLI smoke"
CLI_OUTPUT="$(php -d error_reporting=E_ALL -d display_errors=1 app/commands/command.php 2>&1 || true)"
printf '%s\n' "$CLI_OUTPUT" | sed -n '1,25p'

if printf '%s\n' "$CLI_OUTPUT" | grep -Eiq "$PATTERN"; then
    echo "[ERROR] CLI smoke reported compatibility issues"
    printf '%s\n' "$CLI_OUTPUT" | grep -Ein "$PATTERN" || true
    FAIL=1
fi

echo "[4/4] HTTP smoke"
HOST="${PHP_COMPAT_HOST:-127.0.0.1}"
PORT="${PHP_COMPAT_PORT:-8099}"
ENDPOINTS="${PHP_COMPAT_ENDPOINTS:-/ /sendmail /json /api /api/1 /user/1 /root /chat}"

HTTP_OUT_LOG="$(mktemp /tmp/divak-php85-http-out.XXXXXX.log)"
HTTP_ERR_LOG="$(mktemp /tmp/divak-php85-http-err.XXXXXX.log)"

php -d error_reporting=E_ALL -d display_errors=1 -S "${HOST}:${PORT}" -t public >"$HTTP_OUT_LOG" 2>"$HTTP_ERR_LOG" &
SERVER_PID=$!

cleanup() {
    kill "$SERVER_PID" >/dev/null 2>&1 || true
    wait "$SERVER_PID" 2>/dev/null || true
    rm -f "$HTTP_OUT_LOG" "$HTTP_ERR_LOG"
}
trap cleanup EXIT INT TERM

READY=0
for _ in 1 2 3 4 5 6 7 8 9 10; do
    if curl -sS "http://${HOST}:${PORT}/" >/dev/null 2>&1; then
        READY=1
        break
    fi
done

if [ "$READY" -ne 1 ]; then
    echo "[ERROR] Built-in PHP server did not start on ${HOST}:${PORT}"
    FAIL=1
fi

for endpoint in $ENDPOINTS; do
    URL="http://${HOST}:${PORT}${endpoint}"
    RESPONSE="$(curl -sS -i "$URL" 2>&1 || true)"

    STATUS_LINE="$(printf '%s\n' "$RESPONSE" | head -n 1)"
    echo "- ${endpoint} -> ${STATUS_LINE}"

    if ! printf '%s\n' "$STATUS_LINE" | grep -Eq '^HTTP/[0-9.]+\s+[0-9]{3}\s+'; then
        echo "  [ERROR] transport-level failure while requesting ${URL}"
        FAIL=1
        continue
    fi

    if printf '%s\n' "$RESPONSE" | grep -Eiq "$PATTERN"; then
        echo "  [ERROR] issue markers found in response"
        printf '%s\n' "$RESPONSE" | grep -Ein "$PATTERN" || true
        FAIL=1
    fi
done

if grep -Eiq "$PATTERN" "$HTTP_ERR_LOG"; then
    echo "[ERROR] issue markers found in PHP server stderr"
    grep -Ein "$PATTERN" "$HTTP_ERR_LOG" || true
    FAIL=1
fi

if [ "$FAIL" -ne 0 ]; then
    echo "PHP 8.5 compatibility check failed"
    exit 1
fi

echo "PHP 8.5 compatibility check passed"
