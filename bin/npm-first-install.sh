#!/usr/bin/env bash

npm install
npm install cypress
npx playwright install --with-deps
npm audit fix --force
npm run build

exec "$@"
