#!/usr/bin/env bash

if [ -z "$1" ]; then
  echo "Usage: $0 <directory_path>"
  exit 1
fi

DIRECTORY=$1

if [ ! -d "$DIRECTORY" ]; then
  echo "Directory $DIRECTORY does not exist."
  exit 1
fi

find "$DIRECTORY" -type f | while read -r FILE; do
  if [ -s "$FILE" ] && [ "$(tail -c 1 "$FILE")" != "" ]; then
    echo "" >> "$FILE"
    echo "Added empty line to $FILE"
  fi
done

echo "Processing complete."
