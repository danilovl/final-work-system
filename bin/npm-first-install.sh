#!/usr/bin/env bash

npm install
npm install cypress
npx playwright install --with-deps
npm run build

exec "$@"
