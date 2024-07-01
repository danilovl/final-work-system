#!/usr/bin/env bash

URL="http://elasticsearch:9200/"
RETRIES=20
INTERVAL=10

for i in $(seq 1 $RETRIES); do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" $URL -u elastic:password)
  echo "Attempt $i - URL Status: $STATUS"

  if [ "$STATUS" = "200" ]; then
    echo "URL is accessible, continuing."
    exit 0
  fi

  if [ $i -lt $RETRIES ]; then
    echo "Waiting $INTERVAL seconds before the next attempt..."
    sleep $INTERVAL
  fi
done

echo "URL is not accessible after $RETRIES attempts."
exit 1
