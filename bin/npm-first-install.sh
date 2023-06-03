#!/usr/bin/env bash

npm install
npm audit fix --force
npm run build

exec "$@"