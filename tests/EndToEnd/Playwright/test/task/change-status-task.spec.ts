import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Change status task success ajax', async ({page}) => {
        await page.goto('/en/work/task/list')

        const switches = page.locator('.js-switch')
        const count = Math.min(await switches.count() / 2, 6)

        for (let i = 1; i < count; i++) {
            const responsePromise = page.waitForResponse(response => {
                return response.request().method() === 'POST' && response.url().includes('/ajax')
            })

            await switches.nth(i).click()
            const response = await responsePromise

            expect(response.ok()).toBe(true)
            await expect(page.locator('.alert-success').first()).toBeVisible()
        }
    })
}
