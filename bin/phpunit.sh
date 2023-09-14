#!/usr/bin/env bash

test=$1

php tests/ReplacerFinal.php replace
#XDEBUG_MODE=coverage vendor/bin/phpunit $test --coverage-html coverage-report
vendor/bin/phpunit $test
php tests/ReplacerFinal.php replaceBack
