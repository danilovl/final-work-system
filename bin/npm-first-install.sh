#!/usr/bin/env bash

npm install
npx playwright install --with-deps
npm run build

exec "$@"
