import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
    testDir: './tests/Playwright/test',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: 'html',
    use: {
        baseURL: 'http://nginx:80',
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
