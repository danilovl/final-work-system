import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Change user profile image success', async ({page}) => {
        await page.goto('/en/user/profile/image')

        await page
            .locator('#user_profile_image_uploadMedia')
            .setInputFiles('tests/Cypress/fixtures/user/image/profile.jpg')

        await page.locator('#upload-profile-image-button-action').click()
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
