#!/usr/bin/env bash

php bin/console cache:clear > /dev/null 2>&1 || true
php bin/console cache:warmup > /dev/null 2>&1 || true

