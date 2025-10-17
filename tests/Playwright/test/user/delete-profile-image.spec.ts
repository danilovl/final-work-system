import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {})

    test('Delete user profile image success', async ({page}) => {
        await page.goto('/en/user/profile/image')

        await page.locator('#delete-profile-image').click()

        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
