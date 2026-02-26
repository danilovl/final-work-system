import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {passwordData} from '@playwright-test/fixtures/user/user-change-password'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Change user password success', async ({page}) => {
        await page.goto('/en/user/profile/change-password')

        for (const prop in passwordData) {
            const propKey = prop as keyof typeof passwordData

            await page.fill(
                passwordData[propKey].id,
                passwordData[propKey].text.toString()
            )
        }

        await page.click('#user-change-password-button-action')
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
