#!/usr/bin/env bash

npm install
npm install cypress --save-dev
npm audit fix --force
npm run build

exec "$@"
