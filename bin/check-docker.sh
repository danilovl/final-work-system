#!/usr/bin/env bash

URL=$1
RETRIES=${2:-30}
INTERVAL=${3:-10}
USER="elastic"
PASS="password"

timestamp() { date +"%Y-%m-%d %H:%M:%S"; }

# parse host and port from URL
_host=$(echo "$URL" | sed -E 's#^[a-zA-Z]+://([^/:]+).*#\1#')
_port=$(echo "$URL" | sed -E 's#^[a-zA-Z]+://[^/:]+:([0-9]+).*#\1#')
scheme=$(echo "$URL" | sed -E 's#^([a-zA-Z]+)://.*#\1#')

if [ -z "$_port" ]; then
  if [ "$scheme" = "https" ]; then _port=443; else _port=80; fi
fi

echo "$(timestamp) Checking URL: $URL (host=$_host port=$_port) retries=$RETRIES interval=${INTERVAL}s"

for i in $(seq 1 $RETRIES); do
  echo "=== Attempt $i ($(timestamp)) ==="

  # run curl, save body and stderr; -sS to show errors on stderr
  STATUS=$(curl -sS -o /tmp/curl.body -w "%{http_code}" --connect-timeout 5 -m 15 -u "${USER}:${PASS}" "$URL" 2>/tmp/curl.err) || STATUS=000
  echo "Attempt $i - URL Status: $STATUS"

  # show a little of the response body (if any)
  if [ -s /tmp/curl.body ]; then
    echo "--- response body (first 200 lines) ---"
    sed -n '1,200p' /tmp/curl.body
    echo "--- end response body ---"
  else
    echo "No response body saved."
  fi

  # show curl stderr if present
  if [ -s /tmp/curl.err ]; then
    echo "--- curl stderr (first 200 lines) ---"
    sed -n '1,200p' /tmp/curl.err
    echo "--- end curl stderr ---"
  fi

  # DNS / hosts checks
  echo "--- DNS / hosts checks ---"
  if command -v getent >/dev/null 2>&1; then
    echo "getent hosts $_host:"
    getent hosts "$_host" || true
  elif command -v dig >/dev/null 2>&1; then
    echo "dig +short $_host:"
    dig +short "$_host" || true
  elif command -v nslookup >/dev/null 2>&1; then
    echo "nslookup $_host:"
    nslookup "$_host" || true
  else
    echo "/etc/hosts (head):"
    sed -n '1,50p' /etc/hosts || true
  fi

  # quick ping if available
  if command -v ping >/dev/null 2>&1; then
    echo "ping -c1 $_host (may fail if ICMP blocked):"
    ping -c1 -W 1 "$_host" || true
  fi

  # TCP connect check using bash /dev/tcp
  echo "TCP connect check to $_host:$_port"
  if (exec 3<>/dev/tcp/"$_host"/"$_port") >/dev/null 2>&1; then
    echo "TCP connect to $_host:$_port succeeded"
    exec 3>&- 3<&-
  else
    echo "TCP connect to $_host:$_port failed (connection refused or no route)"
  fi

  # If 200 -> success
  if [ "$STATUS" = "200" ]; then
    echo "$(timestamp) URL is accessible, continuing."
    exit 0
  fi

  # If not last try -> wait
  if [ $i -lt $RETRIES ]; then
    echo "Waiting $INTERVAL seconds before the next attempt..."
    sleep $INTERVAL
  fi
done

echo "URL is not accessible after $RETRIES attempts."
exit 1