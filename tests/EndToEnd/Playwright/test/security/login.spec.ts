import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import LoginPage from '@playwright-test/page/login-page'
import loginData from '@playwright-test/test-data/login'

export default function createTests() {
    test.beforeEach(async ({page}) => {
        await page.context().clearCookies()
        await page.goto('/en/login')
    })

    test('Login failed', async ({page}) => {
        const loginPageInstance = new LoginPage(page)
        await loginPageInstance.login(
            loginData.failed.username,
            loginData.failed.password
        )

        await expect(page.locator('#error-login-message')).toBeVisible()
    })

    test('Login succeed', async ({page}) => {
        const loginPageInstance = new LoginPage(page)
        await loginPageInstance.login(
            loginData.supervisor.username,
            loginData.supervisor.password
        )

        await expect(page.locator('#sidebar-menu')).toBeVisible()
    })
}
