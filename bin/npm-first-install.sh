#!/usr/bin/env bash

npm install
npm install cypress --force
npx playwright install --with-deps
npm run build

exec "$@"
