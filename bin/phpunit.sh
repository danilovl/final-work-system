#!/usr/bin/env bash

test=$1

php tests/ReplacerFinal.php replace
vendor/bin/phpunit tests/$test
php tests/ReplacerFinal.php replaceBack
