import {test as base, Page} from '@playwright/test';
import loginData from '@playwright-test/test-data/login';
import LoginPage from '@playwright-test/page/login-page';

const test = base.extend<{
    loginSupervisor: Page;
    loginStudent: Page;
    login: (username: string, password: string) => Promise<void>;
}>({
    loginSupervisor: async (
        {page}: { page: Page },
        use: (page: Page) => Promise<void>
    ) => {
        const loginPageInstance = new LoginPage(page);
        await loginPageInstance.login(
            loginData.supervisor.username,
            loginData.supervisor.password
        );

        await use(page);
    },
    loginStudent: async (
        {page}: { page: Page },
        use: (page: Page) => Promise<void>
    ) => {
        const loginPageInstance = new LoginPage(page);
        await loginPageInstance.login(
            loginData.student.username,
            loginData.student.password
        );

        await use(page);
    }
});

export {test};
