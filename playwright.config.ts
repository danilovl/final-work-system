import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
    testMatch: [
        '/tests/Playwright/tests.ts'
    ],
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: 'html',
    use: {
        baseURL: 'http://nginx:80',
        extraHTTPHeaders: {
            'X-E2E-TEST': '1'
        },
        actionTimeout: 10000,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'on' // retain-on-failure
    },
    projects: [
        {
            name: 'chromium',
            use: {...devices['Desktop Chrome']}
        }
    ],
});
