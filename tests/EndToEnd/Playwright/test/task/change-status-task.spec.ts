import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Change status task success ajax', async ({page}) => {
        await page.goto('/en/work/task/list')

        const switches = page.locator('.switchery')
        const count = Math.min(await switches.count() / 2, 6)

        for (let i = 1; i < count; i++) {
            await switches.nth(i).click()
            await expect(page.locator('.alert-success')).toBeVisible()
            await page.waitForTimeout(2000)
        }
    })
}
