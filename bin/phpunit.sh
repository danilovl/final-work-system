#!/usr/bin/env bash

test=$1

php -d pcov.enabled=1 vendor/bin/phpunit $test --coverage-html coverage-report
#vendor/bin/phpunit $test
phpunit_exit_code=$?

exit $phpunit_exit_code
