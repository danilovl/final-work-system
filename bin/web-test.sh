#!/usr/bin/env bash

output=$(php tests/Functional/Web/Test/Web.php showEcho )
IFS=$'\n' read -r -d '' -a classNames <<< "$output"

exit_code=0

for className in "${classNames[@]}"
do
  echo "vendor/bin/phpunit --testsuite tests-functional-web --filter '$className'"
  vendor/bin/phpunit --testsuite tests-functional-web --filter "'$className'" || exit_code=1
done

exit $exit_code
