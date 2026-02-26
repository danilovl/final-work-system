#!/usr/bin/env bash

output=$(php tests/Functional/Web/Test/Web.php showEcho )
IFS=$'\n' read -r -d '' -a classNames <<< "$output"

for className in "${classNames[@]}"
do
  vendor/bin/phpunit --filter "'$className'"
  echo "vendor/bin/phpunit --filter '$className'"
done
