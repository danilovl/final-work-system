import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
    testMatch: [
        '/tests/EndToEnd/Playwright/tests.ts'
    ],
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: 'html',
    use: {
        baseURL: 'http://nginx:80',
        storageState: {
            cookies: [{
                name: 'e2e_test',
                value: '1',
                domain: 'nginx',
                path: '/',
                expires: -1,
                httpOnly: false,
                secure: false,
                sameSite: 'Lax'
            }],
            origins: []
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
